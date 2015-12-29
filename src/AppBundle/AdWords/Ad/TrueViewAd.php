<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\AdWords\Ad;

/**
 * Class TrueViewAd
 * TrueView in-search video ad
 * Promote your YouTube videos on the YouTube search network.
 * @see https://developers.google.com/adwords/api/docs/appendix/templateads#pyv_ad_on_search
 * @package AppBundle\AdWords
 */
class TrueViewAd extends TemplateAd
{

    private $headline = 'Video Ad Sample';
    private $description1 = 'description1';
    private $description2 = 'description2';

    private $thumbnail = 0;
    private $destinationPage = 1;
    private $channel = 'EasyToBook';
    private $imageUrl = 'http://scf.etb.ht/graphics/new-design/logos/logo_primary_white.png';
    private $videoMediaId;

    public function __construct($id, $name, $videoMediaId, $displayUrl, $finalUrls)
    {
        parent::__construct($id, $name, "TemplateAd", 231, $displayUrl, $finalUrls);
        $this->videoMediaId = $videoMediaId;
    }


    /**
     * @return string
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * @return string
     */
    public function getDescription1()
    {
        return $this->description1;
    }

    /**
     * @return string
     */
    public function getDescription2()
    {
        return $this->description2;
    }

    /**
     * @return int
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @return int
     */
    public function getDestinationPage()
    {
        return $this->destinationPage;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return mixed
     */
    public function getVideoMediaId()
    {
        return $this->videoMediaId;
    }

}