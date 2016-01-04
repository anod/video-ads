<?php
/**
 * @author alex
 * @date 2015-12-31
 *
 */

namespace AppBundle\Facebook;


class Post
{
    private $id;
    private $title;
    private $picture;
    private $source;
    private $published = 0;
    private $callToAction = [
        'type' => 'BOOK_TRAVEL',
        'value' => ['link' => 'http://example.com']
    ];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param int $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return array
     */
    public function getCallToAction()
    {
        return $this->callToAction;
    }

    /**
     * @param array $callToAction
     */
    public function setCallToAction($callToAction)
    {
        $this->callToAction = $callToAction;
    }


}