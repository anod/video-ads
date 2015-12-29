<?php
/**
 * @author alex
 * @date 2015-12-24
 *
 */

namespace AppBundle\AdWords\Ad;


class TemplateAd
{
    private $id;
    private $name = 'Ad for TrueView';
    private $displayUrl = 'www.easytobook.com';
    private $finalUrls = array('http://www.easytobook.com/video');

    private $type;
    private $templateId;

    /**
     * Ad constructor.
     * @param $id
     * @param string $name
     * @param $type
     * @param $templateId
     * @param string $displayUrl
     * @param null $finalUrls
     */
    public function __construct($id, $name, $type, $templateId = null, $displayUrl = null, $finalUrls = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->displayUrl = $displayUrl;
        $this->type = $type;
        $this->templateId = $templateId;
        $this->finalUrls = $finalUrls;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDisplayUrl()
    {
        return $this->displayUrl;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return array|null
     */
    public function getFinalUrls()
    {
        return $this->finalUrls;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param null|string $displayUrl
     */
    public function setDisplayUrl($displayUrl)
    {
        $this->displayUrl = $displayUrl;
    }

    /**
     * @param array|null $finalUrls
     */
    public function setFinalUrls($finalUrls)
    {
        $this->finalUrls = $finalUrls;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param null $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }


}