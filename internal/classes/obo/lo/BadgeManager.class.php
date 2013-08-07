<?php
/**
 * This class handles all logic for Learning Objects
 * @author Jacob Bates <jbates@mail.ucf.edu>
 * @author Luis Estrada <lestrada@mail.ucf.edu>
 */

namespace obo\lo;
class BadgeManager extends \rocketD\db\DBEnabled
{
	private static $instance;

	function __construct()
	{
		$this->defaultDBM();
	}

	static public function getInstance()
	{
		if(!isset(self::$instance))
		{
			$selfClass = __CLASS__;
			self::$instance = new $selfClass();
		}
		return self::$instance;
	}

	public function getBadgeInfo($loID, $instID)
	{
		$badge = $this->getBadgeForLO($loID);
		if(!$badge)
		{
			return false;
		}

		// Determine the maximum attempt score
		$SM = \obo\ScoreManager::getInstance();
		$scores = $SM->getScoresForUser($instID, $_SESSION['userID']);
		$scoreValues = $SM->calculateUserOverallScoreForInstance($scores);
		$maxScore = $scoreValues['max'];

		$awarded = $maxScore >= $badge->{\cfg_obo_Badge::MIN_SCORE};
		$badgeInfo = array(
			'minScore' => $badge->{\cfg_obo_Badge::MIN_SCORE},
			'awarded' =>  $awarded,
			'params' => $awarded ? $this->getSignedCredHubParams($badge) : false,
		);

		return $badgeInfo;
	}

	/**
	 * Returns badge information for a given LO
	 * @param  int $loID
	 * @return object Object containing badge information (badge id, minimum required score) or false if no badge exists
	 */
	private function getBadgeForLO($loID)
	{
		$qstr = "
					SELECT *
					FROM ".\cfg_obo_Badge::TABLE."
					WHERE ".\cfg_obo_LO::ID." = '?'
					LIMIT 1";

		$q = $this->DBM->querySafe($qstr, $loID);
		if(!$q)
		{
			trace(mysql_error(), true);
			$DBM->rollback();
			return false;
		}

		if($badge = $this->DBM->fetch_obj($q))
		{
			return $badge;
		}

		return false;
	}

	/**
	 * Determines if, based on your attempt history, you should be awarded a badge
	 * (given one exists for a given LO). Badges are awarded based on your highest
	 * attempt score.
	 *
	 * Returns false if this LO doesn't have a badge, otherwise, returns an array of
	 * key/value pairs for creating a signed Credhub request.
	 *
	 * For convience, additionally sets the values of minRequiredScore and awarded
	 * so you don't have to request the badge information a second time.
	 *
	 * @param  int   $loID
	 * @param  int   $instID
	 * @param  int   $minRequiredScore Will contain the minimum required score to obtain the badge
	 * @param  bool  $awarded          True if badge should be awarded, false otherwise
	 * @return array Array of key/value pairs to make a Credhub request (or false if no badge)
	 */
	private function getSignedCredHubParams($badge)
	{
		if(!$badge)
		{
			return false;
		}

		$UM = \rocketD\auth\AuthManager::getInstance();
		$userInfo = $UM->fetchUserByID($_SESSION['userID']);
		$params = array(
			'email' => $userInfo->email,
			'badge_id' => $badge->{\cfg_obo_Badge::BADGE_ID},
		);

		return $this->signOAuthPostArgs($params);
	}

	/**
	 * Returns an array of arguments needed to sign and send an OAuth request via a form POST
	 * NOTE: Requires oauth pecl library
	 * @param  string $endpoint url that the request is going to be sent to
	 * @param  array  $params   array of paramaters to send to the endpoint (excluding any oauth params)
	 * @param  [type] $key      OAuth Consumer Key to send
	 * @param  [type] $secret   OAuth Secret key used to sign the request
	 * @return array            array of key/value pairs to send via Post to the endpoint
	 */
	private function signOAuthPostArgs($params)
	{
		$key = \AppCfg::CREDHUB_KEY;
		$secret = \AppCfg::CREDHUB_SECRET;

		// generate the timestamp and nonce
		$time = time();
		$nonce = uniqid();

		// set up all the required params -this is mostly so they exist when we return them
		// OAuth will create a signature with them even if we dont include them here
		$oauth_params = array(
			'oauth_nonce'            => $nonce,
			'oauth_consumer_key'     => $key,
			'oauth_timestamp'        => $time,
			'oauth_nonce'            => $nonce,
			'oauth_version'          => '1.0',
			'oauth_signature_method' => 'HMAC-SHA1',
		);

		// combine our oauth params with our input
		$params = array_merge($params, $oauth_params);

		// build and sign the oauth variables
		$oauth = new \OAuth($key, $secret);
		$oauth->setTimestamp($time);
		$oauth->setNonce($nonce);
		$oauth->setAuthType(OAUTH_AUTH_TYPE_FORM);
		$signature = $oauth->generateSignature('POST', $endpoint, $params);

		// add the signature back into the params
		$params['oauth_signature'] = $signature;

		return $params;
	}
}
?>