<?php

namespace AppBundle\Controller;

use AppBundle\AdWords\Ad\InStreamVideoAd;
use AppBundle\AdWords\Api;
use AppBundle\AdWords\Campaign;
use AppBundle\AdWords\Config;
use AppBundle\AdWords\Media\Image;
use AppBundle\AdWords\Media\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdWordsController
 * @Route("/adwords")
 * @package AppBundle\Controller
 */
class AdWordsController extends Controller
{

    /**
     * @Route("/campaigns", name="adwords_overview")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Request $request)
    {
        $api = $this->adwords();

        $campaign = new Campaign(0, 'New campaign '.date("Y-m-d H:i:s"));
        $campaignForm = $this->createFormBuilder($campaign)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Add Campaign'))
            ->getForm();

        $campaignForm->handleRequest($request);

        if ($campaignForm->isSubmitted() && $campaignForm->isValid()) {
            $api->addCampaign($campaign);
            return $this->redirectToRoute("adwords_overview");
        }


        $campaigns = $api->loadCampaigns();
        $media = $api->loadMedia();

        return $this->render('AppBundle:AdWords:list.html.twig', array(
            'campaigns' => $campaigns,
            'media' => $media,
            'config' => $api->getConfig(),
            'form' => $campaignForm->createView()
        ));
    }

    /**
     * @Route("/campaigns/{campaignId}/adgroup/add", name="adwords_adgroup_add")
     * @param $campaignId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adGroupAddAction($campaignId, Request $request)
    {

        $this->adwords()->addAdGroup($campaignId);
        return $this->redirectToRoute("adwords_overview");
    }

    /**
     * @Route("/campaigns/{campaignId}/adgroup/{adGroupId}/add", name="adwords_ad_add")
     * @param $campaignId
     * @param $adGroupId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adAddAction($campaignId, $adGroupId, Request $request) {

        $api = $this->adwords();

        $media = $api->loadMedia();

        $ad = new InStreamVideoAd(0, 'In stream Ad '.date("Y-m-d H:i:s"), null, null);
        $ad->setDisplayUrl('www.easytobook.com');
        $ad->setFinalUrls(['http://www.easytobook.com/video']);

        $form = $this->createFormBuilder($ad)
            ->add('name', TextType::class)
            ->add('videoMediaId', ChoiceType::class, [
                'choices' => self::formatMediaChoices($media, 'Video')
            ])
            ->add('companionBanner', ChoiceType::class, [
                'choices' => self::formatMediaChoices($media, 'Image')
            ])
            ->add('displayUrl', TextType::class, [

            ])
            ->add('finalUrls', CollectionType::class, [
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('save', SubmitType::class, array('label' => 'Add Ad'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $api->addTemplateAd($adGroupId, $ad);
            return $this->redirectToRoute("adwords_overview");
        }

        return $this->render('AppBundle:AdWords:add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    private static function formatMediaChoices(array $media, $type) {
        $formatted = [];
        /* @var $item Media */
        foreach ($media as $item) {
            if ($item->getType() != $type) {
                continue;
            }
            $title = '#'.$item->getMediaId().' - '.$item->getName();
            if ($item instanceof Image) {
                $dimens = $item->getDimensions();
                $title.=' '.$dimens['FULL']->width.' x '.$dimens['FULL']->height;
            }
            $formatted[$title] = $item->getMediaId();
        }

        return $formatted;
    }


    /**
     * TODO: define as service
     * @return Api
     */
    protected function adwords() {
        $config = new Config();
        $api = new Api($this->get('logger'), $config);
        return $api;
    }




}
