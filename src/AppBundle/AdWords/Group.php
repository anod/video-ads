<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\AdWords;


class Group
{
    private $id;
    private $name;
    private $ads = [];

    /**
     * Campaign constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    public function addAd(Ad\TemplateAd $ad)
    {
        $this->ads[$ad->getId()] = $ad;
    }

    public function getAds() {
        return $this->ads;
    }

    public function setAds(array $ads)
    {
        $this->ads = $ads;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}