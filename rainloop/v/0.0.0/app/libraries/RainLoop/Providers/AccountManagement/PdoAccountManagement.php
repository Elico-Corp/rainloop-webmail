<?php
namespace RainLoop\Providers\AccountManagement;

use \RainLoop\Providers\AccountManagement\Enumerations\PropertyType;

class PdoAccountManagement
    extends \RainLoop\Common\PdoAbstract
    implements \RainLoop\Providers\AccountManagement\AccountManagementInterface
{
    /**
     * @var string
     */
    private $sDsn;

    /**
     * @var string
     */
    private $sDsnType;

    /**
     * @var string
     */
    private $sUser;

    /**
     * @var string
     */
    private $sPassword;

    public function __construct($sDsn, $sUser = '', $sPassword = '', $sDsnType = 'mysql')
    {
        $this->sDsn = $sDsn;
        $this->sUser = $sUser;
        $this->sPassword = $sPassword;
        $this->sDsnType = $sDsnType;

        $this->bExplain = false;
    }

    /**
     * @return bool
     */
    public function IsSupported()
    {
        $aDrivers = \class_exists('PDO') ? \PDO::getAvailableDrivers() : array();
        return \is_array($aDrivers) ? \in_array($this->sDsnType, $aDrivers) : false;
    }

    /**
     * @return array
     */
    protected function getPdoAccessData()
    {
        return array($this->sDsnType, $this->sDsn, $this->sUser, $this->sPassword);
    }

    /**
    * @return array
    */
    public function GetEmailAndPassword($sCasUser = '')
    {
       $aResult = array();
       
       //TODO add sLogin string check
       $oStmt = $this->prepareAndExecute('SELECT rl_email, email_password FROM rainloop_users WHERE cas_user = :cas_user',
        array(
            ':cas_user' => array($sCasUser, \PDO::PARAM_STR)
            ));

       if ($oStmt)
       {
        $aFetch = $oStmt->fetchAll(\PDO::FETCH_ASSOC);
        if (\is_array($aFetch) && 0 < \count($aFetch))
            {
                $aResult['email'] = $aFetch[0]['rl_email'];
                $aResult['passwd'] = $aFetch[0]['email_password'];

                return $aResult;
            }
       }
    }

    /**
    * @return array
    */
    public function GetLogin($sEmail = '')
    {
       $aResult = array();

       //TODO add sEmail string check
       $oStmt = $this->prepareAndExecute('SELECT rl_login FROM rainloop_users WHERE rl_email = :rl_email',
        array(
            ':rl_email' => array($sEmail, \PDO::PARAM_STR)
            ));

       if ($oStmt)
       {
        $aFetch = $oStmt->fetchAll(\PDO::FETCH_ASSOC);
        if (\is_array($aFetch) && 0 < \count($aFetch))
            {
                $aResult['login'] = $aFetch[0]['rl_login'];

                return $aResult;
            }
       }
    }


}