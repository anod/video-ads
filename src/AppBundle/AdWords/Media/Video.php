<?php
/**
 * @author alex
 * @date 2015-12-27
 *
 */

namespace AppBundle\AdWords\Media;


class Video extends Media
{
    /**
     * Video constructor.
     * @param $mediaId
     * @param $name
     * @param array $urls
     * @param array $sourceUrl
     */
    public function __construct($mediaId, $name, array $urls=null, $sourceUrl)
    {
        parent::__construct($mediaId, $name, 'Video', $urls, $sourceUrl);
    }

}