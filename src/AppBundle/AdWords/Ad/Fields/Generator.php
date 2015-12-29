<?php
/**
 * @author alex
 * @date 2015-12-28
 *
 */

namespace AppBundle\AdWords\Ad\Fields;


use AppBundle\AdWords\Ad\TemplateAd;
use AppBundle\AdWords\Api;

interface Generator
{
    /**
     * @param TemplateAd $ad
     * @param Api $adwords
     * @return array
     */
    public function generate(TemplateAd $ad, Api $adwords);
}