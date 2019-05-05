<?php

namespace Kanboard\Plugin\OAuth2\User;

use Kanboard\Core\Base;
use Kanboard\Core\User\UserProviderInterface;
use Pimple\Container;

/**
 * GenericOAuth2UserProvider
 *
 * @package  Kanboard\User
 * @author   Frederic Guillot
 */
class GenericOAuth2UserProvider extends Base implements UserProviderInterface
{
    /**
     * @var array
     */
    protected $userData = array();

    /**
     * Constructor
     *
     * @access public
     * @param  Container $container
     * @param  array $user
     */
    public function __construct(Container $container, array $user)
    {
        parent::__construct($container);
        $this->userData = $user;
    }

    /**
     * Return true to allow automatic user creation
     *
     * @access public
     * @return boolean
     */
    public function isUserCreationAllowed()
    {
        return $this->configModel->get('oauth2_account_creation', 0) == 1;
    }

    /**
     * Get username
     *
     * @access public
     * @return string
     */
    public function getUsername()
    {
        if ($this->isUserCreationAllowed()) {
            return $this->getUserData('name');
        }

        return '';
    }

    /**
     * Get external id column name
     *
     * @access public
     * @return string
     */
    public function getExternalIdColumn()
    {
        return 'oauth2_user_id';
    }

    /**
     * Get extra user attributes
     *
     * @access public
     * @return array
     */
    public function getExtraAttributes()
    {
        if ($this->isUserCreationAllowed()) {
            return array(
                'is_ldap_user' => 1,
                'disable_login_form' => 1,
            );
        }

        return array();
    }

    /**
     * Get internal id
     *
     * If a value is returned the user properties won't be updated in the local database
     *
     * @access public
     * @return integer
     */
    public function getInternalId()
    {
        return '';
    }

    /**
     * Get external id
     *
     * @access public
     * @return string
     */
    public function getExternalId()
    {
        return $this->getUserData('id');
    }

    /**
     * Get user role
     *
     * Return an empty string to not override role stored in the database
     *
     * @access public
     * @return string
     */
    public function getRole()
    {
        return '';
    }

    /**
     * Get user full name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->getUserData('name');
    }

    /**
     * Get user email
     *
     * @access public
     * @return string
     */
    public function getEmail()
    {
        return $this->getUserData('email');
    }

    /**
     * Get external group ids
     *
     * A synchronization is done at login time,
     * the user will be member of those groups if they exists in the database
     *
     * @access public
     * @return string[]
     */
    public function getExternalGroupIds()
    {
        //Get the primary and secondary group array
        $primaryGroup = $this->getUserData('primaryGroup');
        $secondaryGroups = $this->getUserData('secondaryGroups');

        //Extrat the group names
        $groups = array();
        $groups[] = $primaryGroup['name'];

        foreach($secondaryGroups as $secondaryGroup){
            $groups[] = $secondaryGroup['name'];
        }

        $groups = array_unique($groups);
        $this->logger->debug('OAuth2: '.$this->getUsername().' groups are '. join(',', $groups));

        foreach ($groups as $group) {
            $this->groupModel->getOrCreateExternalGroupId($group, $group);
        }

        return $groups;
    }

    /**
     * Return true if the account creation is allowed according to the settings
     *
     * @access public
     * @param array $profile
     * @return bool
     */
    public function isAccountCreationAllowed(array $profile)
    {
        if ($this->isUserCreationAllowed()) {
            $domains = $this->configModel->get('oauth2_email_domains');

            if (! empty($domains)) {
                return $this->validateDomainRestriction($profile, $domains);
            }

            return true;
        }

        return false;
    }

    /**
     * Validate domain restriction
     *
     * @access private
     * @param  array  $profile
     * @param  string $domains
     * @return bool
     */
    public function validateDomainRestriction(array $profile, $domains)
    {
        foreach (explode(',', $domains) as $domain) {
            $domain = trim($domain);

            if (strpos($profile['email'], $domain) > 0) {
                return true;
            }
        }

        return false;
    }

    protected function getKey($key)
    {
        $key = $this->configModel->get($key);
        return ! empty($key) && isset($this->userData[$key]) ? $this->userData[$key] : '';
    }

    protected function getUserData($key)
    {
        return ! empty($key) && isset($this->userData[$key]) ? $this->userData[$key] : '';
    }
}
