<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

/**
 * An authentication adapter for AuthComponent.  Provides the ability to authenticate using COOKIE
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Cookie' => array(
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password'
 *	 		),
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1)
 *		)
 *	)
 * }}}
 *
 */
class CookieAuthenticate extends BaseAuthenticate {

/**
 * Authenticates the identity contained in the cookie.  Will use the `settings.userModel`, and `settings.fields`
 * to find COOKIE data that is used to find a matching record in the `settings.userModel`.  Will return false if
 * there is no cookie data, either username or password is missing, of if the scope conditions have not been met.
 *
 * @param CakeRequest $request The unused request object
 * @param CakeResponse $response Unused response object.
 * @return mixed.  False on login failure.  An array of User data on success.
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		if (!isset($this->_Collection->Cookie) || !$this->_Collection->Cookie instanceof CookieComponent) {
			throw new CakeException('CookieComponent is not loaded');
		}
		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);

		$fields = $this->settings['fields'];
		$data = $this->_Collection->Cookie->read($model);

		if (empty($data)) {
			return false;
		}
		if (
			empty($data[$fields['username']]) ||
			empty($data[$fields['password']])
		) {
			return false;
		}
		return $this->_findUser(
			$data[$fields['username']],
			$data[$fields['password']]
		);
	}

}
