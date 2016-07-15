<?php

class SaveMessageDriver 
	extends \RainLoop\Common\PdoAbstract
	implements \RainLoop\Providers\SaveMessage\SaveMessageInterface
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
		\Rainloop\ChromePhp::log('__construct');
		$this->sDsn = $sDsn;
		$this->sUser = $sUser;
		$this->sPassword = $sPassword;
		$this->sDsnType = $sDsnType;

		$this->bExplain = false; // debug
	}

	/**
	 * @return array
	 */
	protected function getPdoAccessData()
	{
		return array($this->sDsnType, $this->sDsn, $this->sUser, $this->sPassword);
	}

	private function getInitialTablesArray($sDbType)
	{

		switch ($sDbType)
		{
			case 'mysql':

				$sInitial = <<<MYSQLINITIAL

CREATE TABLE IF NOT EXISTS rainloop_ab_message (

	id_message      bigint UNSIGNED     NOT NULL AUTO_INCREMENT,
	id_message_str  varchar(128)        NOT NULL DEFAULT '',
	id_user         int UNSIGNED        NOT NULL,
	subject         varchar(255)        NOT NULL DEFAULT '',
	changed         int UNSIGNED        NOT NULL DEFAULT 0,
	deleted         tinyint UNSIGNED    NOT NULL DEFAULT 0,
	etag            varchar(128) /*!40101 CHARACTER SET ascii COLLATE ascii_general_ci */ NOT NULL DEFAULT '',

	PRIMARY KEY(id_message),
	INDEX id_user_rainloop_ab_message_index (id_user)

)/*!40000 ENGINE=INNODB *//*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

MYSQLINITIAL;
				break;
		}

		if (0 < \strlen($sInitial))
		{
			$aList = \explode(';', \trim($sInitial));
			foreach ($aList as $sV)
			{
				$sV = \trim($sV);
				if (0 < \strlen($sV))
				{
					$aResult[] = $sV;
				}
			}
		}
		\Rainloop\ChromePhp::log($aResult);
		return $aResult;
	}

	/**
	 * @return bool
	 */
	public function SyncDatabase()
	{
		// \Rainloop\ChromePhp::log('SyncDatabase');
		static $mCache = null;
		if (null !== $mCache)
		{
			return $mCache;
		}

		$mCache = false;
		switch ($this->sDsnType)
		{
			case 'mysql':
				$mCache = $this->dataBaseUpgrade($this->sDsnType.'-ab-version', array(
					8 => $this->getInitialTablesArray($this->sDsnType)
				));
				break;
		}
		\Rainloop\ChromePhp::log('mCache'.$mCache);
		return $mCache;
	}

	/**
	 * @param string $sEmail
	 * @param \RainLoop\Providers\AddressBook\Classes\Contact $oContact
	 * @param bool $bSyncDb = true
	 *
	 * @return bool
	 */
	public function MailSave2($oMessage, $bSyncDb = true)
	{
		\Rainloop\ChromePhp::log($oMessage);
		if ($bSyncDb)
		{
			$this->SyncDatabase();
		}

		$sBcc =  $oMessage['Bcc'];
		$sCc =  $oMessage['Cc'];
		$sDateTimeStampInUTC =  $oMessage['DateTimeStampInUTC'];
		$sDeliveredTo =  $oMessage['DeliveredTo'];
		$sExternalProxy =  $oMessage['ExternalProxy'];
		$sFolder =  $oMessage['Folder'];
		$aFrom =  $oMessage['From'];
		$bHasAttachments =  $oMessage['HasAttachments'];
		$sHash =  $oMessage['Hash'];
		$bIsAnswered =  $oMessage['IsAnswered'];
		$bIsDeleted =  $oMessage['IsDeleted'];
		$bIsFlagged =  $oMessage['IsFlagged'];
		$bIsForwarded =  $oMessage['IsForwarded'];
		$bIsReadReceipt =  $oMessage['IsReadReceipt'];
		$bIsSeen =  $oMessage['IsSeen'];
		$sMessageId =  $oMessage['MessageId'];
		$iPriority =  $oMessage['Priority'];
		$sReadReceipt =  $oMessage['ReadReceipt'];
		$oReplyTo =  $oMessage['ReplyTo'];
		$sRequestHash =  $oMessage['RequestHash'];
		$oSender =  $oMessage['Sender'];
		$iSensitivity =  $oMessage['Sensitivity'];
		$sSubject =  $oMessage['Subject'];
		$aSubjectParts =  $oMessage['SubjectParts'];
		$bTextPartIsTrimmed =  $oMessage['TextPartIsTrimmed'];
		$aThreads =  $oMessage['Threads'];
		$aTo =  $oMessage['To'];
		$iUserID =  $oMessage['Uid'];

		$iIdMessage = 0; //< \strlen($oMessage['IdMessage']) && \is_numeric($oMessage->IdMessage) ? (int) $oMessage->IdMessage : 0;
		$bUpdate = false;//< $iIdMessage;

		\Rainloop\ChromePhp::log('-------------');
		\Rainloop\ChromePhp::log('iUserID '.$iUserID);
		\Rainloop\ChromePhp::log('iIdMessage '.$iIdMessage);
		\Rainloop\ChromePhp::log('sMessageId '.$sMessageId);
		\Rainloop\ChromePhp::log('sSubject '.$sSubject);
		\Rainloop\ChromePhp::log('bUpdate '.$bUpdate);
		\Rainloop\ChromePhp::log('-------------');

		try
		{
			$aFreq = array();
			if ($bUpdate)
			{
				\Rainloop\ChromePhp::log('--------update-----');
				// $aFreq = $this->getMessageFreq($iUserID, $iIdMessage);
				$sSql = 'UPDATE rainloop_ab_message SET id_message_str = :id_contact_str, subject = :subject '.
					'WHERE id_user = :id_user AND id_message = :id_message';

				$this->prepareAndExecute($sSql,
					array(
						':id_user' => array($iUserID, \PDO::PARAM_INT),
						':id_message' => array($iIdMessage, \PDO::PARAM_INT),
						':id_message_str' => array($sMessageId, \PDO::PARAM_STR),
						':subject' => array($sSubject, \PDO::PARAM_STR)
					)
				);

			}
			else
			{
				\Rainloop\ChromePhp::log('-------insert------');
				$sSql = 'INSERT INTO rainloop_ab_message '.
					'( id_user,  id_message_str,  subject)'.
					' VALUES '.
					'(:id_user, :id_message_str, :subject)';

				$this->prepareAndExecute($sSql,
					array(
						':id_user' => array($iUserID, \PDO::PARAM_INT),
						// ':id_message' => array($iIdMessage, \PDO::PARAM_INT),
						':id_message_str' => array($sMessageId, \PDO::PARAM_STR),
						':subject' => array($sSubject, \PDO::PARAM_STR)
					)
				);

				$sLast = $this->lastInsertId('rainloop_ab_message', 'id_message');
				if (\is_numeric($sLast) && 0 < (int) $sLast)
				{
					$iIdMessage = (int) $sLast;
					$oMessage->IdMessage = (string) $iIdMessage;
				}
			}

		}
		catch (\Exception $oException)
		{
			throw $oException;
		}

		return true;
	}

	/**
	 * @param string $sEmail
	 * @param \RainLoop\Providers\AddressBook\Classes\Contact $oContact
	 * @param bool $bSyncDb = true
	 *
	 * @return bool
	 */
	public function MailSave($sEmail, &$oContact, $bSyncDb = true)
	{
		// \Rainloop\ChromePhp::log('MailSave');
		
		if ($bSyncDb)
		{
			$this->SyncDatabase();
		}

		$iUserID = $this->getUserId($sEmail);

		$iIdContact = 0 < \strlen($oContact->IdContact) && \is_numeric($oContact->IdContact) ? (int) $oContact->IdContact : 0;

		$bUpdate = 0 < $iIdContact;

		$oContact->UpdateDependentValues();
		$oContact->Changed = \time();

		try
		{
			$aFreq = array();
			if ($bUpdate)
			{
				$aFreq = $this->getContactFreq($iUserID, $iIdContact);

				$sSql = 'UPDATE rainloop_ab_mail SET id_contact_str = :id_contact_str, display = :display, changed = :changed, etag = :etag '.
					'WHERE id_user = :id_user AND id_contact = :id_contact';

				$this->prepareAndExecute($sSql,
					array(
						':id_user' => array($iUserID, \PDO::PARAM_INT),
						':id_contact' => array($iIdContact, \PDO::PARAM_INT),
						':id_contact_str' => array($oContact->IdContactStr, \PDO::PARAM_STR),
						':display' => array($oContact->Display, \PDO::PARAM_STR),
						':changed' => array($oContact->Changed, \PDO::PARAM_INT),
						':etag' => array($oContact->Etag, \PDO::PARAM_STR)
					)
				);

				// clear previos props
				// $this->prepareAndExecute(
				//     'DELETE FROM rainloop_ab_properties WHERE id_user = :id_user AND id_contact = :id_contact',
				//     array(
				//         ':id_user' => array($iUserID, \PDO::PARAM_INT),
				//         ':id_contact' => array($iIdContact, \PDO::PARAM_INT)
				//     )
				// );
			}
			else
			{
				$sSql = 'INSERT INTO rainloop_ab_mail '.
					'( id_user,  id_contact_str,  display,  changed,  etag)'.
					' VALUES '.
					'(:id_user, :id_contact_str, :display, :changed, :etag)';

				$this->prepareAndExecute($sSql,
					array(
						':id_user' => array($iUserID, \PDO::PARAM_INT),
						':id_contact_str' => array($oContact->IdContactStr, \PDO::PARAM_STR),
						':display' => array($oContact->Display, \PDO::PARAM_STR),
						':changed' => array($oContact->Changed, \PDO::PARAM_INT),
						':etag' => array($oContact->Etag, \PDO::PARAM_STR)
					)
				);

				$sLast = $this->lastInsertId('rainloop_ab_mail', 'id_contact');
				if (\is_numeric($sLast) && 0 < (int) $sLast)
				{
					$iIdContact = (int) $sLast;
					$oContact->IdContact = (string) $iIdContact;
				}
			}

			if (0 < $iIdContact)
			{
				$aParams = array();
				foreach ($oContact->Properties as /* @var $oProp \RainLoop\Providers\AddressBook\Classes\Property */ $oProp)
				{
					$iFreq = $oProp->Frec;
					if ($oProp->IsEmail() && isset($aFreq[$oProp->Value]))
					{
						$iFreq = $aFreq[$oProp->Value];
					}

					$aParams[] = array(
						':id_contact' => array($iIdContact, \PDO::PARAM_INT),
						':id_user' => array($iUserID, \PDO::PARAM_INT),
						':prop_type' => array($oProp->Type, \PDO::PARAM_INT),
						':prop_type_str' => array($oProp->TypeStr, \PDO::PARAM_STR),
						':prop_value' => array($oProp->Value, \PDO::PARAM_STR),
						':prop_value_lower' => array($oProp->ValueLower, \PDO::PARAM_STR),
						':prop_value_custom' => array($oProp->ValueCustom, \PDO::PARAM_STR),
						':prop_frec' => array($iFreq, \PDO::PARAM_INT),
					);
				}

				if (0 < \count($aParams))
				{
					$sSql = 'INSERT INTO rainloop_ab_properties '.
						'( id_contact,  id_user,  prop_type,  prop_type_str,  prop_value,  prop_value_lower, prop_value_custom,  prop_frec)'.
						' VALUES '.
						'(:id_contact, :id_user, :prop_type, :prop_type_str, :prop_value, :prop_value_lower, :prop_value_custom, :prop_frec)';

					$this->prepareAndExecute($sSql, $aParams, true);
				}
			}
		}
		catch (\Exception $oException)
		{
			throw $oException;
		}

		return 0 < $iIdContact;
	}
}