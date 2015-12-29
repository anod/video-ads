<?php
/**
 * @author alex
 * @date 2015-12-27
 *
 */

namespace AppBundle\AdWords\Ad;

/**
 * Class InStreamVideoAd
 * In-stream video ad
 * Non-skippable 15 or 20 second video ad within video publisher content.
 * @see https://developers.google.com/adwords/api/docs/appendix/templateads#instream_video
 * @package AppBundle\AdWords
 */
class InStreamVideoAd extends TemplateAd
{
    protected $videoMediaId;
    protected $companionBanner;

    /**
     * InStreamVideoAd constructor.
     * @param $id
     * @param string $name
     * @param $videoMediaId
     * @param string $companionBanner
     */
    public function __construct($id, $name, $videoMediaId, $companionBanner)
    {
        parent::__construct($id, $name, "TemplateAd", 49);
        $this->videoMediaId = $videoMediaId;
        $this->companionBanner = $companionBanner;
    }

    /**
     * @return mixed
     */
    public function getVideoMediaId()
    {
        return $this->videoMediaId;
    }

    /**
     * @return string
     */
    public function getCompanionBanner()
    {
        return $this->companionBanner;
    }

    /**
     * @param mixed $videoMediaId
     */
    public function setVideoMediaId($videoMediaId)
    {
        $this->videoMediaId = $videoMediaId;
    }

    /**
     * @param string $companionBanner
     */
    public function setCompanionBanner($companionBanner)
    {
        $this->companionBanner = $companionBanner;
    }


}