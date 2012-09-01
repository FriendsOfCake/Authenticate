<?php
App::uses('BasicAuthenticate', 'Controller/Component/Auth');

class KerberosAuthenticate extends BasicAuthenticate {

/**
 * Get a user based on information in the request.  Used by cookie-less auth for stateless clients.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
	public function getUser($request) {
		$username = env('REMOTE_USER');

		if (empty($username)) {
			return false;
		}
		return $this->_findUser(array('User.username' => $username));
	}

/**
 * Generate the login headers
 *
 * @return string Headers for logging in.
 */
	public function loginHeaders() {
		return sprintf('WWW-Authenticate: Kerberos realm="%s"', $this->settings['realm']);
	}
}