<?php
App::uses('FormAuthenticate', 'Controller/Component/Auth');
App::uses('GoogleAuthenticator', 'Authenticate.Lib');
/**
 * An authentication adapter for AuthComponent.  Provides the ability to authenticate using POST
 * data. The username form input can be checked against multiple table columns, for instance username and email
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Google' => array(
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password',
 *				'code' => 'code',
 *				'secret' => 'secret'
 *	 		),
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1)
 *		)
 *	)
 * }}}
 *
 */
class GoogleAutenticate extends BaseAuthenticate {

/**
 * Settings for this object.
 *
 * - `fields` The fields to use to identify a user by.
 * - 'columns' array of columns to check username form input against
 * - `userModel` The model name of the User, defaults to User.
 * - `scope` Additional conditions to use when looking up and authenticating users,
 *    i.e. `array('User.is_active' => 1).`
 *
 * @var array
 */
	public $settings = array(
		'fields' => array(
			'username' => 'username',
			'password' => 'password',
			'code' => 'code',
			'secret' => 'secret'
		),
		'userModel' => 'User',
		'scope' => array()
	);


/**
 * Checks the fields to ensure they are supplied.
 *
 * @param CakeRequest $request The request that contains login information.
 * @param string $model The model used for login verification.
 * @param array $fields The fields to be checked.
 * @return boolean False if the fields have not been supplied. True if they exist.
 */
	protected function _checkFields(CakeRequest $request, $model, $fields) {
		if (empty($request->data[$model])) {
			return false;
		}
		if (
			empty($request->data[$model][$fields['username']]) ||
			empty($request->data[$model][$fields['password']]) ||
			empty($request->data[$model][$fields['secret']])
		) {
			return false;
		}
		return true;
	}

/**
 * Authenticates the identity contained in a request.  Will use the `settings.userModel`, and `settings.fields`
 * to find POST data that is used to find a matching record in the `settings.userModel`.  Will return false if
 * there is no post data, either username or password is missing, of if the scope conditions have not been met.
 *
 * @param CakeRequest $request The request that contains login information.
 * @param CakeResponse $response Unused response object.
 * @return mixed.  False on login failure.  An array of User data on success.
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);

		$fields = $this->settings['fields'];
		if (!$this->_checkFields($request, $model, $fields)) {
			return false;
		}
		$user = $this->_findUser(
			array(
			    $model . '.' . $fields[$this->settings['username']] => $request->data[$model][$fields['username']],
			    $model . '.' . $fields[$this->settings['password']] => $request->data[$model][$fields['password']]
			)
		);
		if (!$user) {
		    return false;
		}

		if(empty($user[$model][$fields['secret']])) {
		    return $user;
		}

		$Google = new GoogleAuthenticator();

		return $Google->getCode($user[$model][$fields['secret']]) == $request->data[$model][$fields['code']] ? $user : false;
	}

}
