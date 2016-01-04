<?php
/**
 * @author alex
 * @date 2015-12-29
 *
 */

namespace AppBundle\Controller;

use AppBundle\Facebook\Api as AdApi;
use AppBundle\Facebook\FacebookLoggerWrapper;
use AppBundle\Facebook\Post;
use FacebookAds;
use FacebookAds\Object\AdUser;
use FacebookAds\Object\Fields\AdFields;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use FacebookAds\Object\AdAccount;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Client;

/**
 * Class AdWordsController
 * @Route("/facebook")
 * @package AppBundle\Controller
 */
class FacebookController extends Controller
{
    const APP_ID = '968249236580526';
    const APP_SECRET = '1dcf4aa2c735fa96f4ba191db7f64efa';
    const PAGE_ID = 1014874405244008;

    /**
     * @Route("/add", name="fb_add")
     */
    public function addAction(Request $request) {

        $fb = new Facebook([
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
        ]);


        $helper = $fb->getRedirectLoginHelper();

        $destUrl = $this->generateUrl('fb_add', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $loginUrl = $helper->getLoginUrl($destUrl, ['ads_management','manage_pages', 'publish_pages']);
        $access_token = $this->get('session')->get('facebook_access_token');
        if (!$access_token) {
            $access_token = (string) $helper->getAccessToken();
            if ($access_token) {
                $this->get('session')->set('facebook_access_token', $access_token);
            }
        }


        if ($access_token) {
            $fbApi = FacebookAds\Api::init(self::APP_ID, self::APP_SECRET, $access_token);
            $fbApi->setLogger(new FacebookLoggerWrapper($this->get('logger')));

            $me = new AdUser('me');
            $adAccount = $me->getAdAccounts()->current();

            $post = new Post();
            $post->setTitle('Test Video Ad '.date('Y-m-d H:i:s'));
            $post->setPicture('https://upload.wikimedia.org/wikipedia/commons/1/17/Westie_pups.jpg');

            $form = $this->createFormBuilder($post)
                ->add('title', TextType::class)
                ->add('picture', UrlType::class)
                ->add('source', FileType::class)
                ->add('save', SubmitType::class, array('label' => 'Add Campaign'))
                ->getForm();


            $form->handleRequest($request);

            $newAdUrl = $newAdId = null;
            if ($form->isSubmitted() && $form->isValid()) {

                $client = $this->createHttpClient();
                $this->uploadVideo($post);

                $adAccountId = $adAccount->getData()[AdFields::ACCOUNT_ID];

                $ad = new AdApi($adAccountId, self::PAGE_ID, $access_token, $client);
                $addId = $ad->addAd($post);
                $newAdId = $addId;
                $newAdUrl = sprintf('https://www.facebook.com/ads/manager/ad/ads/?act=%s&pid=p2&ids=%s',
                    $adAccountId,
                    $newAdId
                );

            }

            return $this->render('AppBundle:AdWords:facebook_add.html.twig', array(
                'form' => $form->createView(),
                'newAdUrl' => $newAdUrl,
                'newAdId' => $newAdId,
                'loginUrl' => $loginUrl,
                'destUrl' => $destUrl,
                'appId' => self::APP_ID
            ));
        } else {

            return $this->render('AppBundle:AdWords:facebook_login.html.twig', array(
                'loginUrl' => $loginUrl,
                'destUrl' => $destUrl,
                'appId' => self::APP_ID
            ));
        }

    }

    private function createHttpClient() {
        $stack = HandlerStack::create();

        $format = MessageFormatter::CLF . "\n\n<<<<<<<<\n{response}\n--------\n{error}";
        $stack->push(Middleware::log($this->get('logger'), new MessageFormatter($format)));
        return new Client(['handler' => $stack]);
    }

    private function uploadVideo(Post $post) {
        // $file stores the uploaded PDF file
        /** @var UploadedFile $file */
        $file = $post->getSource();

        // Generate a unique name for the file before saving it
        $fileName = $file->getClientOriginalName();

        // Move the file to the directory where brochures are stored
        $uploadDir = $this->getParameter('kernel.root_dir').'/../web/uploads/videos';

        $file->move($uploadDir, $fileName);

        // Update the 'brochure' property to store the PDF file name
        // instead of its contents
        $post->setSource($uploadDir . '/' . $fileName);

    }
}