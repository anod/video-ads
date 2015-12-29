<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\AdWords;


use AppBundle\AdWords\Ad\Fields\Factory;
use Psr\Log\LoggerInterface;

class Api
{
    const ADWORDS_VERSION = 'v201509';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \AdWordsUser
     *
     */
    private $user;

    /**
     * Api constructor.
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(LoggerInterface $logger, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    private function init() {
        if ($this->user == null) {
            $this->user = $this->createUser();
        }
    }

    public function getAccounts() {
        $this->init();
        /* @var $managedCustomerService \ManagedCustomerService */
        $managedCustomerService = $this->user->GetService('ManagedCustomerService',  self::ADWORDS_VERSION);
        $page = $managedCustomerService->get( new \Selector(['CustomerId', 'Name']));

        $accounts = array();
        foreach ($page->entries as $account) {
            if ($account->customerId > 0) {
                $accounts[] = $account;
            }
        }

        return $accounts;
    }

    public function loadAdGroups($campaignId) {
        $this->init();

        /* @var $adGroupService \AdGroupService */
        $adGroupService = $this->user->GetService('AdGroupService', self::ADWORDS_VERSION);
        $selector = new \Selector(['Id', 'Name']);

        // Create predicates.
        $selector->predicates[] =
            new \Predicate('CampaignId', 'IN', [$campaignId]);

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

        $addGroups = [];
        do {
            // Make the get request.
            $page = $adGroupService->get($selector);

            // Display results.
            if (!isset($page->entries)) {
                break;
            }

            foreach ($page->entries as $entry) {
                $adGroup = new Group((int)$entry->id,$entry->name);
                $adGroup->setAds($this->loadGroupAds($adGroup->getId()));
                $addGroups[$adGroup->getId()] = $adGroup;
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $addGroups;
    }

    public function loadGroupAds($adGroupId) {
        $this->init();

        /* @var $adGroupService \AdGroupAdService */
        $adGroupService = $this->user->GetService('AdGroupAdService', self::ADWORDS_VERSION);
        $selector = new \Selector(['Id', 'Name']);
        $selector->predicates[] =
            new \Predicate('AdGroupId', 'IN', [$adGroupId]);

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

        $ads = [];
        do {
            // Make the get request.
            $page = $adGroupService->get($selector);

            // Display results.
            if (!isset($page->entries)) {
                break;
            }

            foreach ($page->entries as $entry) {
                $ads[] = new Ad\TemplateAd($entry->ad->id, $entry->ad->name, $entry->ad->AdType, $entry->ad->templateId, $entry->ad->displayUrl, $entry->ad->finalUrls);
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $ads;
    }

    public function loadCampaigns() {
        $this->init();
        // Get the service, which loads the required classes.
        /* @var $campaignService \CampaignService */
        $campaignService = $this->user->GetService('CampaignService', self::ADWORDS_VERSION);

        // Create selector.
        $selector = new \Selector(['Id', 'Name'], null, null, new \OrderBy('Name', 'ASCENDING'));

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

        $campaigns = [];
        do {
            // Make the get request.
            $page = $campaignService->get($selector);

            // Display results.
            if (!isset($page->entries)) {
                break;
            }

            foreach ($page->entries as $campaignRemote) {
                $campaign = new Campaign($campaignRemote->id, $campaignRemote->name);
                $campaign->setAdGroups($this->loadAdGroups($campaign->getId()));
                $campaigns[] = $campaign;
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $campaigns;
    }

    public function addBudget() {
        /* @var $budgetService \BudgetService */
        $budgetService = $this->user->GetService('BudgetService', self::ADWORDS_VERSION);

        // Create the shared budget (required).
        $budget = new \Budget(null, 'Budget ' . uniqid());
        $budget->period = 'DAILY';
        $budget->amount = new \Money(50000000);
        $budget->deliveryMethod = 'STANDARD';

        // Create operation.
        $operation = new \BudgetOperation($budget, 'ADD');

        // Make the mutate request.
        $result = $budgetService->mutate([$operation]);
        return $result->value[0]->budgetId;
    }

    public function addCampaign(Campaign $campaign) {
        $this->init();

        $budgetId = $this->addBudget();

        /* @var $campaignService \CampaignService */
        $campaignService = $this->user->GetService('CampaignService', self::ADWORDS_VERSION);

        // Create campaign.
        $remote = new \Campaign(null, $campaign->getName());
        $remote->advertisingChannelType = 'SEARCH';

        // Set shared budget (required).
        $remote->budget = new \Budget($budgetId);

        // Set bidding strategy (required).
        $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->biddingStrategyType = 'MANUAL_CPC';

        // You can optionally provide a bidding scheme in place of the type.
        $biddingScheme = new \ManualCpcBiddingScheme();
        $biddingScheme->enhancedCpcEnabled = false;
        $biddingStrategyConfiguration->biddingScheme = $biddingScheme;

        $remote->biddingStrategyConfiguration = $biddingStrategyConfiguration;

        // Set network targeting (optional).
        $networkSetting = new \NetworkSetting();
        $networkSetting->targetGoogleSearch = true;
        $networkSetting->targetSearchNetwork = true;
        $networkSetting->targetContentNetwork = true;
        $remote->networkSetting = $networkSetting;

        // Set additional settings (optional).
        $remote->status = 'PAUSED';
        $remote->startDate = date('Ymd', strtotime('+1 day'));
        $remote->endDate = date('Ymd', strtotime('+1 month'));
        $remote->adServingOptimizationStatus = 'ROTATE';

        // Set frequency cap (optional).
        $frequencyCap = new \FrequencyCap();
        $frequencyCap->impressions = 5;
        $frequencyCap->timeUnit = 'DAY';
        $frequencyCap->level = 'ADGROUP';
        $remote->frequencyCap = $frequencyCap;

        // Set advanced location targeting settings (optional).
        $geoTargetTypeSetting = new \GeoTargetTypeSetting();
        $geoTargetTypeSetting->positiveGeoTargetType = 'DONT_CARE';
        $geoTargetTypeSetting->negativeGeoTargetType = 'DONT_CARE';
        $remote->settings[] = $geoTargetTypeSetting;

        // Create operation.
        $operation = new \CampaignOperation($remote, 'ADD');

        // Make the mutate request.
        $result = $campaignService->mutate([$operation]);

        $newCampaign = $result->value[0];
        return new Campaign($newCampaign->id, $newCampaign->name);
    }

    public function addAdGroup($campaignId) {
        $this->init();

        /* @var $adGroupService \AdGroupService */
        $adGroupService = $this->user->GetService('AdGroupService', self::ADWORDS_VERSION);

        // Create ad group.
        $adGroup = new \AdGroup();
        $adGroup->campaignId = $campaignId;
        $adGroup->name = 'Test Ad Group ' . uniqid();

        // Set bids (required).
        $bid = new \CpcBid();
        $bid->bid =  new \Money(1000000);
        $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->bids[] = $bid;
        $adGroup->biddingStrategyConfiguration = $biddingStrategyConfiguration;

        // Set additional settings (optional).
        $adGroup->status = 'ENABLED';

        // Targeting restriction settings - these settings only affect serving
        // for the Display Network.
        $targetingSetting = new \TargetingSetting();
        // Restricting to serve ads that match your ad group placements.
        // This is equivalent to choosing "Target and bid" in the UI.
        $targetingSetting->details[] =
            new \TargetingSettingDetail('PLACEMENT', false);
        // Using your ad group verticals only for bidding. This is equivalent
        // to choosing "Bid only" in the UI.
        $targetingSetting->details[] =
            new \TargetingSettingDetail('VERTICAL', true);
        $adGroup->settings[] = $targetingSetting;

        // Create operation.
        $operation = new \AdGroupOperation($adGroup, 'ADD');

        // Make the mutate request.
        $result = $adGroupService->mutate([$operation]);

        // Display result.
        $newAddGroup = $result->value[0];
        return new Group($newAddGroup->id,$newAddGroup->name);
    }

    private function createUser() {

        $user = new \AdWordsUser(null, $this->config->getDeveloperToken(), $this->config->getUserAgent(), null, null, $this->config->getOauth2Info());

        $user->SetClientCustomerId($this->config->getClientCustomerId());

        LoggerStreamWrapper::setLogger($this->logger);

        $handler = LoggerStreamWrapper::register();
        \Logger::LogToStream(\Logger::$SOAP_XML_LOG, $handler);
        \Logger::LogToStream(\Logger::$REQUEST_INFO_LOG, $handler);
        \Logger::LogToStream(\ReportUtils::$LOG_NAME, $handler);

        // Log every SOAP XML request and response.
        $user->LogAll();

        return $user;
    }


    /**
     * @return array|Media\Media[]
     */
    public function loadMedia() {
        $this->init();
        /* @var $mediaService \MediaService */
        $mediaService = $this->user->GetService('MediaService', self::ADWORDS_VERSION);

        // Create selector.
        $selector = new \Selector(['MediaId', 'Width', 'Height', 'MimeType', 'Name', 'Urls', 'SourceUrl']);
        $selector->ordering = array(new \OrderBy('Type', 'ASCENDING'));
        // Create predicates.
        $selector->predicates[] =
            new \Predicate('Type', 'IN', array('IMAGE', 'VIDEO'));

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

        $media = [];
        do {
            // Make the get request.
            $page = $mediaService->get($selector);

            // Display images.
            if (isset($page->entries)) {
                foreach ($page->entries as $mediaEntry) {
                    $urls = $mediaEntry->urls ? \MapUtils::GetMap($mediaEntry->urls) : [];
                    if ($mediaEntry->MediaType == 'Image') {
                        $dimensions = \MapUtils::GetMap($mediaEntry->dimensions);
                        $media[] = new Media\Image($mediaEntry->mediaId, $mediaEntry->name, $urls, $mediaEntry->sourceUrl, $mediaEntry->mimeType, $dimensions);
                    } else if ($mediaEntry->MediaType == 'Video') {
                        $media[] = new Media\Video($mediaEntry->mediaId, $mediaEntry->name, $urls, $mediaEntry->sourceUrl);
                    }
                }
            } else {
                break;
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $media;
    }

    public function addTemplateAd($adGroupId, Ad\TemplateAd $ad, $status = 'PAUSED') {
        $this->init();

        $fields = Factory::create($ad)->generate($ad, $this);

        /* @var $adGroupAdService \AdGroupAdService */
        $adGroupAdService = $this->user->GetService('AdGroupAdService', self::ADWORDS_VERSION);

        // Create the template ad.
        $templateAd= new \TemplateAd();
        $templateAd->name = $ad->getName();
        $templateAd->templateId = $ad->getTemplateId();
        $templateAd->finalUrls = $ad->getFinalUrls();
        $templateAd->displayUrl = $ad->getDisplayUrl();
        $templateAd->templateElements = [new \TemplateElement('adData', $fields)];

        // Create the ad group ad.
        $adGroupAd = new \AdGroupAd($adGroupId, $templateAd);
        $adGroupAd->status = $status;

        // Create the operation.
        $operation = new \AdGroupAdOperation($adGroupAd, null, 'ADD');

        // Create the ads.
        $result = $adGroupAdService->mutate([$operation]);

        return $result;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getUser()
    {
        $this->init();
        return $this->user;
    }

    public function getService($service)
    {
        return $this->user->GetService($service, self::ADWORDS_VERSION);
    }
}