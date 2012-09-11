<?php
App::uses('BasicAuthenticate', 'Controller/Component/Auth');

class KerberosAuthenticate extends BasicAuthenticate {

/**
 * Get a user based on information in the request.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
	public function getUser($request) {
		$username = env('REMOTE_USER');

		if (empty($username)) {
			throw new UnauthorizedException('Username required');
		}
		$user = $this->_findUser(array('User.username' => $username));
		if ($user) {
			return $user;
		}
		throw new UnauthorizedException('Incorrect username');
	}

}