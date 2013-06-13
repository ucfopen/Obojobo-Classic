<?
namespace Lti;

class Data
{
	const ROLE_CONTENT_DEVELOPER = 'ContentDeveloper';
	const ROLE_ADMINISTRATOR     = 'Administrator';
	const ROLE_INSTRUCTOR        = 'Instructor';
	const ROLE_LEARNER           = 'Learner';
	const ROLE_STUDENT           = 'Student';

	public $sourceId;
	public $serviceUrl;
	public $resourceId;
	public $consumerId;
	public $consumer;
	public $email;
	public $last;
	public $first;
	public $fullName;
	public $roles;
	public $remoteId;
	public $username;
	public $isSelectorMode;
	public $returnUrl;
	public $timestamp;
	public $contextId;
	public $contextTitle;

	public function __construct($data)
	{
		$this->oauthNonce     = static::get($data, 'oauth_nonce');
		$this->sourceId       = static::get($data, 'lis_result_sourcedid');
		$this->serviceUrl     = static::get($data, 'lis_outcome_service_url');
		$this->resourceId     = static::get($data, 'resource_link_id');
		$this->consumerId     = static::get($data, 'tool_consumer_instance_guid');
		$this->consumer       = static::get($data, 'tool_consumer_info_product_family_code');
		$this->email          = static::get($data, 'lis_person_contact_email_primary');
		$this->last           = static::get($data, 'lis_person_name_family');
		$this->first          = static::get($data, 'lis_person_name_given');
		$this->fullName       = static::get($data, 'lis_person_name_full');
		$this->remoteId       = static::get($data, \AppCfg::LTI_CANVAS_REMOTE_IDENTIFIER_FIELD);
		$this->username       = static::get($data, \AppCfg::LTI_CANVAS_REMOTE_USERNAME_FIELD);
		$this->timestamp      = static::get($data, 'oauth_timestamp');
		$this->contextId      = static::get($data, 'context_id');
		$this->contextTitle   = static::get($data, 'context_title', 'Unknown Course');
		$this->isSelectorMode = static::get($data, 'selection_directive') === 'select_link';
		$this->returnUrl      = static::get($data, 'launch_presentation_return_url');
		$this->roles          = explode(',', static::get($data, 'roles'));
	}

	public function hasValidUserData()
	{
		return $this->isTestUser() || (!empty($this->remoteId) && !empty($this->username) && !empty($this->email) && !empty($this->consumer));
	}

	public function isTestUser()
	{
		return $this->first === 'Test' && $this->last === 'Student' && $this->isLearner();
	}

	public function hasRole($roles)
	{
		foreach($this->roles as $role)
		{
			if(in_array($role, $roles))
			{
				return true;
			}
		}

		return false;
	}

	public function isLearner()
	{
		return $this->hasRole(array(self::ROLE_LEARNER, self::ROLE_STUDENT));
	}

	public function isInstructor(){
		return $this->hasRole(array(self::ROLE_ADMINISTRATOR, self::ROLE_INSTRUCTOR, self::ROLE_CONTENT_DEVELOPER));
	}

	public function hasValidRole()
	{
		return $this->hasRole(array(self::ROLE_LEARNER, self::ROLE_STUDENT, self::ROLE_INSTRUCTOR, self::ROLE_ADMINISTRATOR, self::ROLE_CONTENT_DEVELOPER));
	}

	private static function get($array, $key, $default = false)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}
}