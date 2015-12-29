<?php
/**
 * @author alex
 * @date 2015-12-28
 *
 */

namespace AppBundle\AdWords\Ad\Fields;


use AppBundle\AdWords\Ad\TemplateAd;
use AppBundle\AdWords\Ad\TrueViewAd;
use AppBundle\AdWords\Api;

class TrueViewFields implements Generator
{
    /**
     * @param TemplateAd $ad
     * @param Api $adwords
     * @return array
     */
    public function generate(TemplateAd $ad, Api $adwords)
    {
        return $this->generateTyped($ad, $adwords);
    }

    private function generateTyped(TrueViewAd $videoAd,Api $adwords)
    {
        // @src https://developers.google.com/adwords/api/docs/appendix/templateads#pyv_ad_on_search
        $fields = [];
        $fields[] = new \TemplateElementField('headline', 'TEXT', $videoAd->getHeadline());
        $fields[] = new \TemplateElementField('description1', 'TEXT', $videoAd->getDescription1());
        $fields[] = new \TemplateElementField('description2', 'TEXT', $videoAd->getDescription2());

        $video = new \Video();
        $video->mediaId = $videoAd->getVideoMediaId();
        $fields[] = new \TemplateElementField('videoId', 'VIDEO', null, $video);

        // 0: Default (default), 1: Thumbnail 1, 2: Thumbnail 2, 3: Thumbnail 3
        $fields[] = new \TemplateElementField('videoThumbnail', 'ENUM', $videoAd->getThumbnail());
        // 1: Play on watch page (default), 2: Play on channel page
        $fields[] = new \TemplateElementField('destinationPage', 'ENUM', $videoAd->getDestinationPage());
        // This is the name of the YouTube channel, for example 'Google' for the youtube.com/Google channel.
        $fields[] = new \TemplateElementField('channelName', 'TEXT', $videoAd->getChannel());
        // The url of a thumbnail to display alongside the ad text.
        $fields[] = new \TemplateElementField('imageUrl', 'URL', $videoAd->getImageUrl());

        return $fields;
    }
}