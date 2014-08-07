<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

/**
 * An authentication adapter for AuthComponent
 *
 * Provides the ability to authenticate using Token
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Token' => array(
 *			'parameter' => '_token',
 *			'header' => 'X-MyApiTokenHeader',
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1)
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password',
 *				'token' => 'public_key',
 *			),
 * 			'continue' => true
 *		)
 *	)
 * }}}
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class TokenAuthenticate extends BaseAuthenticate {

/**
 * Settings for this object.
 *
 * - `parameter` The url parameter name of the token.
 * - `header` The token header value.
 * - `userModel` The model name of the User, defaults to User.
 * - `fields` The fields to use to identify a user by. Make sure `'token'` has been added to the array
 * - `scope` Additional conditions to use when looking up and authenticating users,
 *    i.e. `array('User.is_active' => 1).`
 * - `recursive` The value of the recursive key passed to find(). Defaults to 0.
 * - `contain` Extra models to contain and store in session.
 * - `continue` Continue after trying token authentication or just throw the `unauthorized` exception.
 * - `unauthorized` Exception name to throw or a status code as an integer.
 *
 * @var array
 */
	public $settings = array(
		'parameter' => '_token',
		'header' => 'X-ApiToken',

		'userModel' => 'User',
		'fields' => array(
			'username' => 'username',
			'password' => 'password',
			'token' => 'token',
		),
		'scope' => array(),
		'recursive' => 0,
		'contain' => null,

		'continue' => false,
		'unauthorized' => 'BadRequestException'
	);

/**
 * Constructor
 *
 * @param ComponentCollection $collection The Component collection used on this request.
 * @param array $settings Array of settings to use.
 * @throws CakeException
 */
	public function __construct(ComponentCollection $collection, $settings) {
		parent::__construct($collection, $settings);
		if (empty($this->settings['parameter']) && empty($this->settings['header'])) {
			throw new CakeException(__d('authenticate', 'You need to specify token parameter and/or header'));
		}
	}

/**
 * Implemented because CakePHP forces you to.
 *
 * @param CakeRequest $request The request object.
 * @param CakeResponse $response response object.
 * @return boolean Always false.
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		return false;
	}

/**
 * If unauthenticated, try to authenticate and respond.
 *
 * @param CakeRequest $request The request object.
 * @param CakeResponse $response The response object.
 * @return boolean False on failure, user on success.
 * @throws HttpException or the one specified using $settings['unauthorized']
 */
	public function unauthenticated(CakeRequest $request, CakeResponse $response) {
		if ($this->settings['continue']) {
			return false;
		}
		if (is_string($this->settings['unauthorized'])) {
			// @codingStandardsIgnoreStart
			throw new $this->settings['unauthorized'];
			// @codingStandardsIgnoreEnd
		}
		$message = __d('authenticate', 'You are not authenticated.');
		throw new HttpException($message, $this->settings['unauthorized']);
	}

/**
 * Get token information from the request.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
	public function getUser(CakeRequest $request) {
		if (!empty($this->settings['header'])) {
			$token = $request->header($this->settings['header']);
			if ($token) {
				return $this->_findUser($token, null);
			}
		}
		if (!empty($this->settings['parameter']) && !empty($request->query[$this->settings['parameter']])) {
			$token = $request->query[$this->settings['parameter']];
			return $this->_findUser($token);
		}
		return false;
	}

/**
 * Find a user record.
 *
 * @param string $username The token identifier.
 * @param string $password Unused password.
 * @return Mixed Either false on failure, or an array of user data.
 */
	protected function _findUser($username, $password = null) {
		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);
		$fields = $this->settings['fields'];

		$conditions = array(
			$model . '.' . $fields['token'] => $username,
		);
		if (!empty($this->settings['scope'])) {
			$conditions = array_merge($conditions, $this->settings['scope']);
		}
		$result = ClassRegistry::init($userModel)->find('first', array(
			'conditions' => $conditions,
			'recursive' => (int)$this->settings['recursive'],
			'contain' => $this->settings['contain'],
		));
		if (empty($result) || empty($result[$model])) {
			return false;
		}
		$user = $result[$model];
		unset($user[$fields['password']]);
		unset($result[$model]);
		return array_merge($user, $result);
	}

}
