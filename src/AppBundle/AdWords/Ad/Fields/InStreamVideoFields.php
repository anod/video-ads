<?php
/**
 * @author alex
 * @date 2015-12-28
 *
 */

namespace AppBundle\AdWords\Ad\Fields;


use AppBundle\AdWords\Ad\InStreamVideoAd;
use AppBundle\AdWords\Ad\TemplateAd;
use AppBundle\AdWords\Api;

class InStreamVideoFields implements Generator
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

    private function generateTyped(InStreamVideoAd $ad,Api $adwords)
    {
        $fields = [];

        // Load class definitions
        $adwords->getService('AdGroupAdService');

        $video = new \Video();
        $video->mediaId = $ad->getVideoMediaId();
        $fields[] = new \TemplateElementField('video', 'VIDEO', null, $video);

        $banner = new \Image();
        $banner->mediaId = $ad->getCompanionBanner();
        $fields[] = new \TemplateElementField('companionBanner', 'IMAGE', null, $banner);

        return $fields;
    }
}