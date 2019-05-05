<?php

namespace Kanboard\Plugin\OAuth2\Auth;

use Kanboard\Core\Base;
use Kanboard\Core\Security\OAuthAuthenticationProviderInterface;
use Kanboard\Plugin\OAuth2\User\GenericOAuth2UserProvider;

/**
 * GenericOAuth2Provider
 *
 * @package  Kanboard\Auth
 * @author   Frederic Guillot
 */
class GenericOAuth2Provider extends Base implements OAuthAuthenticationProviderInterface
{
    /**
     * User properties
     *
     * @access private
     * @var GenericOAuth2UserProvider
     */
    private $userInfo = null;

    /**
     * OAuth2 instance
     *
     * @access protected
     * @var \Kanboard\Core\Http\OAuth2
     */
    protected $service;

    /**
     * OAuth2 code
     *
     * @access protected
     * @var string
     */
    protected $code = '';

    /**
     * Get authentication provider name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return 'OAuth2';
    }

    /**
     * Authenticate the user
     *
     * @access public
     * @return boolean
     */
    public function authenticate()
    {
        $profile = $this->getProfile();

        if (! empty($profile)) {
            $this->userInfo = new GenericOAuth2UserProvider($this->container, $profile);
            return true;
        }

        return false;
    }

    /**
     * Set Code
     *
     * @access public
     * @param  string  $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get user object
     *
     * @access public
     * @return GenericOAuth2UserProvider
     */
    public function getUser()
    {
        return $this->userInfo;
    }

    /**
     * Get configured OAuth2 service
     *
     * @access public
     * @return \Kanboard\Core\Http\OAuth2
     */
    public function getService()
    {
        if (empty($this->service)) {
            $this->service = $this->oauth->createService(
                $this->getClientId(),
                $this->getClientSecret(),
                $this->helper->url->to('OAuthController', 'handler', array('plugin' => 'OAuth2'), '', true),
                $this->getOAuthAuthorizeUrl(),
                $this->getOAuthTokenUrl(),
                $this->getScopes()
            );
        }

        return $this->service;
    }

    /**
     * Get user profile
     *
     * @access public
     * @return array
     */
    public function getProfile()
    {
        $token = $this->getService()->getAccessToken($this->code);

        if (DEBUG) {
            $this->logger->debug(__METHOD__.': Got access token: '.(empty($token) ? 'No' : 'Yes'));
            $this->logger->debug(__METHOD__.': Fetch user id from '.$this->getUserAPiUrl());
        }

        $response1 = $this->httpClient->getJson(
            $this->getUserAPiUrl(),
            array($this->getService()->getAuthorizationHeader())
        );
        $userId = $response1["id"];
        $userProfileApiUrl = $this->getUserProfileAPiUrl($userId);

        if(DEBUG){
            $this->logger->debug(__METHOD__.': Got UserId: '.$userId);
            $this->logger->debug(__METHOD__.': Fetch user profile from '.$userProfileApiUrl);
        }
        $userProfileApiUrl = $userProfileApiUrl."?key=".$this->getIPbAPiKey();

        return  $this->httpClient->getJson(
            $userProfileApiUrl,
            array()
        );
    }

    /**
     * Unlink user
     *
     * @access public
     * @param  integer $userId
     * @return bool
     */
    public function unlink($userId)
    {
        return $this->userModel->update(array(
            'id' => $userId,
            'oauth2_user_id' => '',
        ));
    }

    /**
     * Get client id
     *
     * @access public
     * @return string
     */
    public function getClientId()
    {
        return $this->configModel->get('oauth2_client_id');
    }

    /**
     * Get scopes
     *
     * @access public
     * @return array
     */
    public function getScopes()
    {
        return explode(" ", $this->configModel->get('oauth2_scopes'));
    }


    /**
     * Get client secret
     *
     * @access public
     * @return string
     */
    public function getClientSecret()
    {
        return $this->configModel->get('oauth2_client_secret');
    }

    /**
     * Get base url
     *
     * @access public
     * @return string
     */
    public function getIPbBaseUrl()
    {
        return $this->configModel->get('oauth2_ipb_base_url');
    }

    /**
     * Get authorize url
     *
     * @access public
     * @return string
     */
    public function getOAuthAuthorizeUrl()
    {
        return $this->getIPbBaseUrl().'oauth/authorize/';
    }

    /**
     * Get token url
     *
     * @access public
     * @return string
     */
    public function getOAuthTokenUrl()
    {
        return $this->getIPbBaseUrl().'oauth/token/';
    }

    /**
     * Get User API url
     *
     * @access public
     * @return string
     */
    public function getUserAPiUrl()
    {
        return $this->getIPbBaseUrl().'api/core/me';
    }

    /**
     * Get User Profile API url
     *
     * @param $userId ID of the User
     * @access public
     * @return string
     */
    public function getUserProfileAPiUrl($userId)
    {
        return $this->getIPbBaseUrl().'api/core/members/'.$userId;
    }

    /**
     * Get User API Key
     *
     * @access public
     * @return string
     */
    public function getIPbAPiKey()
    {
        return $this->configModel->get('oauth2_ipb_api_key');
    }
}
