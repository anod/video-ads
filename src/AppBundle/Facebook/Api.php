<?php
/**
 * @author alex
 * @date 2015-12-29
 *
 */

namespace AppBundle\Facebook;

use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Values\AdObjectives;

use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Fields\TargetingSpecsFields;
use FacebookAds\Object\Values\BillingEvents;
use FacebookAds\Object\TargetingSpecs;
use FacebookAds\Object\Values\OptimizationGoals;

use FacebookAds\Object\AdCreative;
use FacebookAds\Object\Fields\AdCreativeFields;

use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;
use GuzzleHttp\Client;

class Api
{
    const API_VERSION = 'v2.5';
    protected $adAccountId;
    protected $client;
    protected $pageId;
    protected $appAccessToken;

    public function __construct($adAccountId, $pageId, $appAccessToken, Client $client)
    {
        $this->adAccountId = $adAccountId;
        $this->appAccessToken = $appAccessToken;
        $this->pageId = $pageId;
        $this->client = $client;
    }

    public function requestPageAccessToken() {
        $response = $this->client->request('GET', 'https://graph.facebook.com/me/accounts', [
            'query' => [ 'access_token' => $this->appAccessToken ]
        ]);

        $responseData = json_decode($response->getBody(), true);

       return $responseData['data'][0]['access_token'];
    }

    public function addAd(Post $post)
    {

        $pageAccessToken = $this->requestPageAccessToken();
        $postId = $this->createVideoPost($pageAccessToken, $post);
        $creativeId = $this->createAdCreateive($postId);

        $campaignId = $this->createCampaign();
        $adSetId = $this->createAdSet($campaignId);
        $adId = $this->createAd($adSetId, $creativeId);

        return $adId;
    }

    private function createVideoPost($pageAccessToken, Post $post) {
        $url = sprintf('https://graph-video.facebook.com/%s/%s/videos', self::API_VERSION, $this->pageId);
        $formData = [
            'multipart' => [
                [ 'name' => 'title', 'contents' => $post->getTitle() ],
                [ 'name' => 'picture', 'contents' => $post->getPicture()],
                [ 'name' => 'published', 'contents' => $post->getPublished() ? '1' : '0'],
                [ 'name' => 'call_to_action', 'contents'=> json_encode($post->getCallToAction())],
                [ 'name' => 'access_token', 'contents' => $pageAccessToken],
                [ 'name' => 'source' , 'contents'=> fopen($post->getSource(),'r'), 'filename' => basename($post->getSource()) ]
            ]
        ];

        $response = $this->client->request('POST', $url , $formData);

        $responseData = json_decode($response->getBody(), true);
        $postId = $responseData['id'];


        $pagePostId = $this->verifyPostCreated($postId, $pageAccessToken);

        if (!$pagePostId) {
            throw new \Exception("Post id $postId not found");
        }
        return $pagePostId;
    }

    private function createCampaign()
    {
        $campaign = new Campaign(null, 'act_' . $this->adAccountId);
        $campaign->setData(array(
            CampaignFields::NAME => 'My Campaign ' . date("Y-m-d H:i:s"),
            CampaignFields::OBJECTIVE => AdObjectives::VIDEO_VIEWS,
        ));

        $campaign->create(array(
            Campaign::STATUS_PARAM_NAME => Campaign::STATUS_PAUSED,
        ));

        return $campaign->getData()[Campaign::FIELD_ID];
    }

    private function createAdSet($campaignId)
    {
        $adset = new AdSet(null, 'act_' . $this->adAccountId);
        $adset->setData(array(
            AdSetFields::NAME => 'My Ad Set ' . date("Y-m-d H:i:s"),
            AdSetFields::OPTIMIZATION_GOAL => OptimizationGoals::REACH,
            AdSetFields::BILLING_EVENT => BillingEvents::IMPRESSIONS,
            AdSetFields::BID_AMOUNT => 2,
            AdSetFields::DAILY_BUDGET => 1000,
            AdSetFields::CAMPAIGN_ID => $campaignId,
            AdSetFields::TARGETING => (new TargetingSpecs())->setData(array(
                TargetingSpecsFields::GEO_LOCATIONS => array(
                    'countries' => array('US'),
                ),
            )),
        ));
        $adset->create(array(
            AdSet::STATUS_PARAM_NAME => AdSet::STATUS_PAUSED,
        ));

        return $adset->getData()[AdSet::FIELD_ID];
    }

    private function createAdCreateive($postId)
    {
        $creative = new AdCreative(null, 'act_' . $this->adAccountId);

        $creative->setData(array(
            AdCreativeFields::NAME => 'Sample Promoted Post ' . date("Y-m-d H:i:s"),
            AdCreativeFields::OBJECT_STORY_ID => $this->pageId.'_'.$postId,
        ));

        $creative->create();

        return $creative->getData()[AdCreative::FIELD_ID];
    }

    private function createAd($adSetId, $creativeId)
    {
        $data = array(
            AdFields::NAME => 'My Ad '. date("Y-m-d H:i:s"),
            AdFields::ADSET_ID => $adSetId,
            AdFields::CREATIVE => array(
                'creative_id' => $creativeId,
            ),
        );

        $ad = new Ad(null, 'act_' . $this->adAccountId);
        $ad->setData($data);
        $ad->create(array(
            Ad::STATUS_PARAM_NAME => Ad::STATUS_PAUSED,
        ));

        return $ad->getData()[Ad::FIELD_ID];
    }

    private function verifyPostCreated($postId, $pageAccessToken)
    {
        //Retrieve full post id
        $url = sprintf('https://graph.facebook.com/%s/%s/promotable_posts', self::API_VERSION, $this->pageId);

        // As alternative , webhook can be used to get notified when video post is ready
        // https://developers.facebook.com/docs/graph-api/webhooks/v2.5
        for($i = 0; $i < 10; $i++) {
            usleep(2000000); // 2s

            $response = $this->client->request('GET', $url, ['query' => [
                'access_token' => $pageAccessToken
            ]]);
            $responseData = json_decode($response->getBody(), true);
            if (!isset($responseData['data'])) {
                continue;
            }
            foreach ($responseData['data'] AS $item) {
                if (strpos($item['id'], $postId) !== false) {
                    return $postId;
                }
            }
        }
        return null;
    }
}