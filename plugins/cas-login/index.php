<?php
class CasLoginPlugin extends \RainLoop\Plugins\AbstractPlugin
{   
    /**
     * @var \RainLoop\Providers\AccountManagement
     */
    private $oAccountManagementProvider;

    public function Init()
    {
        // $this->addHook('filter.login-credentials', 'FilterLoginCredentials');
        // $this->addHook('main.farbrica', 'MainFabrica');
        // $this->addHook('filter.app-data', FilterAppData);
        // $this->addHook('filter.http-paths', FilterHttpPaths)
        $this->addHook('filter.application-config', 'FilterApplicationConfig');
        $this->addHook('filter.pre-do-login', 'FilterPreDoLogin');
        $this->addHook('filter.login-credentials.cas-login', 'FilterLoginСredentialsCasLogin');
        $this->addHook('service.after-logout', 'ServiceAfterLogout'); 
        $this->addJs('js/include.js');
    }

    // public function Support()
    // {
    //     if(!\class_exists(phpCas))
    //     {
    //         return 'phpCas extension should be installed before enabling this plugin';
    //     }
    // }

    public function AccountManagementProvider($oConfig)
    {
        $sDsn = \trim($oConfig->get('contacts', 'pdo_dsn', ''));
        $sUser = \trim($oConfig->get('contacts', 'pdo_user', ''));
        $sPassword = (string) $oConfig->get('contacts', 'pdo_password', '');

        $sDsnType = \trim($oConfig->get('contacts', 'type', 'sqlite'));
        
        $oDriver = new \RainLoop\Providers\AccountManagement\PdoAccountManagement($sDsn, $sUser, $sPassword, $sDsnType);

        $oAccountManagementProvider = new \RainLoop\Providers\AccountManagement($oDriver);
        return $oAccountManagementProvider;
    }


    public function FilterApplicationConfig(&$oConfig)
    {
        $sCasHost = \trim($this->Config()->Get('plugin', 'cas_server_host', ''));
        $iCasPort = $this->Config()->Get('plugin', 'cas_server_port', 8443);

        phpCAS::setDebug('/tmp/phpCAS-rl.log'); // Schrijft debug informatie naar een log-file

        // Parameters: CAS version, CAS server url, CAS server port, CAS server URI (same as host), 
        // boolean indicating session start, communication protocol (SAML) between application and CAS server
        phpCAS::client(CAS_VERSION_3_0,$sCasHost, $iCasPort,'', true, 'saml');

        // Server from which logout requests are sent
        // phpCAS::handleLogoutRequests(true, array('cas1.ugent.be','cas2.ugent.be','cas3.ugent.be','cas4.ugent.be','cas5.ugent.be','cas6.ugent.be'));
        phpCAS::handleLogoutRequests(true,array('http://localhost:8080/php_cas_login/home.html'));

        // Path to the "trusted certificate authorities" file:
        // phpCAS::setCasServerCACert('/etc/ssl/certs/ca-certificates.crt');
        // No server verification (less safe!):
        phpCAS::setNoCasServerValidation();
        // The actual user authentication
        phpCAS::forceAuthentication(); 

        $this->oAccountManagementProvider = $this->AccountManagementProvider($oConfig);
    }

    public function FilterPreDoLogin($sLogin, &$sEmail, &$sPassword)
    {
        $sUser = phpCAS::getUser();
        if ($sUser !== '' && $sUser == 'admin')
        {
            // TODO Admin login 
            $sEmail = $sUser;
            $sPassword = '12345';
        }
        else
        {
            $aResult = array();
            $aResult = $this->oAccountManagementProvider->GetEmailAndPassword($sUser);
            $sLogin = $sUser;
            $sEmail = $aResult['email'];
            $sPassword = $aResult['passwd'];
        }

    }

    public function FilterLoginСredentialsCasLogin(&$sEmail, &$sLogin, &$sPassword)
    {
        $bShortLogin = $this->Config()->Get('plugin', 'use_short_login', true);
        if (!empty($sEmail) && $bShortLogin)
        {
            $aResult = $this->oAccountManagementProvider->GetLogin($sEmail);
            if (is_array($aResult) && !empty($aResult['login']))
            {
                $sLogin = $aResult['login'];
            }
        }
    }

    public function ServiceAfterLogout()
    {
        // Handle logout requests
        phpCAS::logout();
    }

    public function configMapping()
    {
        return Array(
            \RainLoop\Plugins\Property::NewInstance('cas_server_host')->SetLabel('cas_server_host')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
                ->SetDescription('The host of cas server service.')
                ->SetDefaultValue('0.0.0.0'),
            \RainLoop\Plugins\Property::NewInstance('cas_server_port')->SetLabel('cas_server_port')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::INT)
                ->SetDescription('The port of server url.')
                ->SetDefaultValue(8443),
                \RainLoop\Plugins\Property::NewInstance('use_short_login')->SetLabel('use_short_login')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
                ->SetDescription('Use short login name.')
                ->SetDefaultValue(true),
            \RainLoop\Plugins\Property::NewInstance('label_3')->SetLabel('lable_3')
                ->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
                ->SetDescription('Throw an label 3 error instead of an access error.')
                ->SetDefaultValue(true)
                ); 
    }
}
