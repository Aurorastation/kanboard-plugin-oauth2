<?php

namespace Kanboard\Plugin\OAuth2;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;
use Kanboard\Plugin\OAuth2\Auth\GenericOAuth2Provider;

class Plugin extends Base
{
    public function initialize()
    {
        $this->authenticationManager->register(new GenericOAuth2Provider($this->container));
        $this->applicationAccessMap->add('OAuthController', 'handler', Role::APP_PUBLIC);

        $this->route->addRoute('/oauth/callback', 'OAuthController', 'handler', 'OAuth2');

        $this->template->hook->attach('template:auth:login-form:after', 'OAuth2:auth/login');
        $this->template->hook->attach('template:config:integrations', 'OAuth2:config/integration');
        $this->template->hook->attach('template:user:external', 'OAuth2:user/external');
        $this->template->hook->attach('template:user:authentication:form', 'OAuth2:user/authentication');
        $this->template->hook->attach('template:user:create-remote:form', 'OAuth2:user/create_remote');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'OAuth2 IPB';
    }

    public function getPluginDescription()
    {
        return t('IPB OAuth2 authentication plugin');
    }

    public function getPluginAuthor()
    {
        return 'Werner Maisl';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/Aurorastation/plugin-oauth2';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}

