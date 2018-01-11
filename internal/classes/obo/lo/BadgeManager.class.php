<?php

namespace obo\lo;
class BadgeManager extends \rocketD\db\DBEnabled
{
	use \rocketD\Singleton;

	/**
	 * Determines if, based on your attempt history, you should be awarded a badge
	 * (given one exists for a given LO). Badges are awarded based on your highest
	 * attempt score.
	 *
	 * Returns false if this LO doesn't have a badge, otherwise, returns an object of
	 * useful information needed to implement Credhub badges.
	 *
	 * @param int $loID
	 * @param int $instID
	 * @return object Object (See code) or false if no badge exists for this lo
	 */
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
			trace($this->DBM->error(), true);
			$this->DBM->rollback();
			return false;
		}

		if($badge = $this->DBM->fetch_obj($q))
		{
			return $badge;
		}

		return false;
	}

	/**
	 * Returns an array of key/value pairs for creating a signed Credhub request.
	 *
	 * @param  object $badge A badge as returned from getBadgeFromLO()
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
			'name' => $userInfo->first,
			'last_name' => $userInfo->last,
		);

		return $this->sign_oauth_post_args(\AppCfg::CREDHUB_URL, $params, \AppCfg::CREDHUB_KEY, \AppCfg::CREDHUB_SECRET);
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
	protected function sign_oauth_post_args($endpoint, array $params, $key, $secret)
	{
		// set up all the required params -this is mostly so they exist when we return them
		// OAuth will create a signature with them even if we dont include them here
		$oauth_params = array(
			'oauth_nonce'            => uniqid(),
			'oauth_consumer_key'     => $key,
			'oauth_timestamp'        => time(),
		);

		// combine our oauth params with our input
		$params = array_merge($params, $oauth_params);

		// build and sign the oauth varia
		$hmcsha1  = new \Eher\OAuth\HmacSha1();
		$consumer = new \Eher\OAuth\Consumer('', $secret);
		$request  = \Eher\OAuth\Request::from_consumer_and_token($consumer, '', 'POST', $endpoint);
		foreach($params as $key => $val)
		{
			$request->set_parameter($key, $val, false);
		}
		$request->sign_request($hmcsha1, $consumer, '');

		return $request->get_parameters();
	}
}
