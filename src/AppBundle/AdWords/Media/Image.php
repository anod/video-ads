<?php
/**
 * @author alex
 * @date 2015-12-27
 *
 */

namespace AppBundle\AdWords\Media;


class Image extends Media
{
    protected $dimensions;
    protected $mimeType;


    /**
     * Video constructor.
     * @param $mediaId
     * @param $name
     * @param array $urls
     * @param array $sourceUrl
     * @param $mimeType
     * @param array $dimensions
     */
    public function __construct($mediaId, $name, array $urls=null, $sourceUrl, $mimeType, array $dimensions)
    {
        parent::__construct($mediaId, $name, 'Image', $urls, $sourceUrl);
        $this->mimeType = $mimeType;
        $this->dimensions = $dimensions;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

}