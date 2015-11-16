<?php

namespace RainLoop\Providers\SaveMessage;

interface SaveMessageInterface
{
	/**
	 * @param string $sDsn
	 * @param string $sUser
	 *@param string $sPassword
	 * @param bool $sDsnType = true
	 */
	public function __construct($sDsn, $sUser = '', $sPassword = '', $sDsnType = 'mysql');

	/**
	 * @return bool
	 */
	public function SyncDatabase();

	/**
	 * @param string $sEmail
	 * @param \RainLoop\Providers\AddressBook\Classes\Contact $oContact
	 * @param bool $bSyncDb = true
	 *
	 * @return bool
	 */
	public function MailSave($sEmail, &$oContact, $bSyncDb = true);

}