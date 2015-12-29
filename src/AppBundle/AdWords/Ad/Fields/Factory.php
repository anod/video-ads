<?php
/**
 * @author alex
 * @date 2015-12-28
 *
 */

namespace AppBundle\AdWords\Ad\Fields;


use AppBundle\AdWords\Ad\InStreamVideoAd;
use AppBundle\AdWords\Ad\TemplateAd;
use AppBundle\AdWords\Ad\TrueViewAd;

class Factory
{
    public static function create(TemplateAd $ad) {
        if ($ad instanceof InStreamVideoAd) {
            return new InStreamVideoFields();
        } elseif ($ad instanceof TrueViewAd) {
            return new TrueViewFields();
        }
        return null;
    }
}