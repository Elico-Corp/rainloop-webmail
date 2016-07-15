<?php
class PdoMessagePlugin extends \RainLoop\Plugins\AbstractPlugin
{
	public function Init()
	{
		$this->addHook('pdo.save-message', 'PdoSaveMessage');
	}
	/**
	 * @param string $oMessageList
	 * @param string $sLogin
	 * @param string $sPassword
	 *
	 * @throws \RainLoop\Exceptions\ClientException
	 */

	/**
	 * @return string
	 */
	public function Supported()
	{
		if (!extension_loaded('pdo') || !class_exists('PDO'))
		{
			return 'The PHP exention PDO (mysql) must be installed to use this plugin';
		}

		$aDrivers = \PDO::getAvailableDrivers();
		if (!is_array($aDrivers) || !in_array('mysql', $aDrivers))
		{
			return 'The PHP exention PDO (mysql) must be installed to use this plugin';
		}
		return '';
	}

	public function PdoSaveMessage(&$aMessages, &$oProvider)
	{
		if (True)
		{
			include_once __DIR__.'/SaveMessageDriver.php';
			$sDsn = \trim($this->Config()->Get('contacts', 'pdo_dsn', 'mysql:host=127.0.0.1;port=3306;dbname=rainloop'));
			$sUser = \trim($this->Config()->Get('contacts', 'pdo_user', 'root'));
			$sPassword = (string) $this->Config()->Get('contacts', 'pdo_password', '12345');
			$sDsnType = 'mysql';
			// $sDsnType = $this->ValidateContactPdoType(\trim($this->Config()->Get('contacts', 'type', 'sqlite')));
			$oProvider = new SaveMessageDriver($sDsn, $sUser, $sPassword, $sDsnType);
			foreach ($aMessages as $oMessage) {
				$oProvider->MailSave2($oMessage);
			}
 			
		}

		
		$resultSaveMessage = false;
		if ($resultSaveMessage)
		{
			throw new \RainLoop\Exceptions\ClientException(\RainLoop\Notifications::AccountNotAllowed);
		}
	}

	// /**
	//  * @return array
	//  */
	// public function configMapping()
	// {
	// 	return array(
	// 		\RainLoop\Plugins\Property::NewInstance('mysql')->SetLabel('mysql')
	// 			->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
	// 			->SetDescription('Use Mysql As Pdo Backend.')
	// 			->SetDefaultValue(true),
	// 		\RainLoop\Plugins\Property::NewInstance('pdo_settings')->SetLabel('Use System Mysql List')
	// 			->SetType(\RainLoop\Enumerations\PluginPropertyType::BOOL)
	// 			->SetDescription('Use System Settings.')
	// 			->SetDefaultValue(true)
	// 	);
	// }

	/**
	 * @return array
	 */
	public function configMapping()
	{
		return array(
			\RainLoop\Plugins\Property::NewInstance('pdo_dsn')->SetLabel('SaveMessage PDO dsn')
				->SetDefaultValue('mysql:host=127.0.0.1;dbname=Rainloop'),
			\RainLoop\Plugins\Property::NewInstance('user')->SetLabel('DB User')
				->SetDefaultValue('root'),
			\RainLoop\Plugins\Property::NewInstance('password')->SetLabel('DB Password')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::PASSWORD)
				->SetDefaultValue('12345'),
			\RainLoop\Plugins\Property::NewInstance('allowed_emails')->SetLabel('Allowed emails')
				->SetType(\RainLoop\Enumerations\PluginPropertyType::STRING_TEXT)
				->SetDescription('Allowed emails, space as delimiter, wildcard supported. Example: user1@domain1.net user2@domain1.net *@domain2.net')
				->SetDefaultValue('*')
		);
	}
}
