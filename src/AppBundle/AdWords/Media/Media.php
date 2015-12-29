<?php
/**
 * @author alex
 * @date 2015-12-27
 *
 */

namespace AppBundle\AdWords\Media;


class Media
{
    protected $urls;
    protected $sourceUrl;
    private $mediaId;
    private $name;
    private $type;

    /**
     * Video constructor.
     * @param $mediaId
     * @param $name
     * @param $type
     * @param array $urls
     * @param $sourceUrl
     */
    public function __construct($mediaId, $name, $type, array $urls=null, $sourceUrl)
    {
        $this->mediaId = $mediaId;
        $this->name = $name;
        $this->type = $type;
        $this->urls = $urls ?: [];
        $this->sourceUrl = $sourceUrl;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @return mixed
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * @return mixed
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}