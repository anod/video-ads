<?php
/**
 * @author alex
 * @date 2015-12-23
 *
 */

namespace AppBundle\AdWords;


class Config
{

    private $developerToken = "EyJgOZ-Y0R8HdIC5pHolhg";
    //private $clientCustomerId = 9398358267; // Test account customer id " Video Ads Test" 
    private $clientCustomerId = 5694212053; // Test account customer id " newtest "
    private $userAgent = "Easytobook";

    private $oauth2Info = [
        //Test account credentials - when going to live generate all for live account
        'client_id' => "410068972363-c0tmsm0sfdqbfneii4vf14gnonuk4avq.apps.googleusercontent.com",
        'client_secret' => "OwXsJgzmqkTmlyFpwONQv2Um",
        'refresh_token' =>"1/5x4g3hOfLR6i4fNdYAv9ogb1yeNP7Jt_NngUV9VqtKE" //created by a script from google "get refresh token" @see /vendor/googleads/googleads-php-lib/examples/AdWords/Auth/GetRefreshTokenwWithoutIniFile.php
    ];


    /**
     * @return string
     */
    public function getDeveloperToken()
    {
        return $this->developerToken;
    }

    /**
     * @return string
     */
    public function getClientCustomerId()
    {
        return $this->clientCustomerId;
    }

    /**
     * @return array
     */
    public function getOauth2Info()
    {
        return $this->oauth2Info;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}