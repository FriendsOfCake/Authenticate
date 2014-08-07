<?php
namespace FOC\Authenticate\Auth\Test\TestCase\Auth;

use Cake\Controller\Controller;
use Cake\Controller\Component\AuthComponent;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use FOC\Authenticate\Auth\TokenAuthenticate;

/**
 * Test case for FormAuthentication
 *
 * @package       Cake.Test.Case.Controller.Component.Auth
 */
class TokenAuthenticateTest extends TestCase {

	public $fixtures = array('plugin.authenticate.multi_user');

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Collection = $this->getMock('ComponentCollection');
		$this->auth = new TokenAuthenticate($this->Collection, array(
			'fields' => array(
				'username' => 'user',
				'password' => 'password',
				'token' => 'token'
			),
			'userModel' => 'MultiUser',
		));
		$password = Security::hash('password', null, true);
		$User = ClassRegistry::init('MultiUser');
		$User->updateAll(array('password' => $User->getDataSource()->value($password)));
		$this->response = $this->getMock('CakeResponse');
	}

/**
 * test authenticate token as query parameter
 *
 * @return void
 */
	public function testAuthenticateTokenParameter() {
		$this->auth->settings['_parameter'] = 'token';
		$request = new CakeRequest('posts/index?_token=54321');

		$result = $this->auth->getUser($request, $this->response);
		$this->assertFalse($result);

		$expected = array(
			'id' => '1',
			'user' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => '2007-03-17 01:16:23',
			'updated' => '2007-03-17 01:18:31'
		);
		$request = new CakeRequest('posts/index?_token=12345');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);

		$this->auth->settings['parameter'] = 'tokenname';
		$request = new CakeRequest('posts/index?tokenname=12345');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);
	}

/**
 * test authenticate token as request header
 *
 * @return void
 */
	public function testAuthenticateTokenHeader() {
		$_SERVER['HTTP_X_APITOKEN'] = '54321';
		$request = new CakeRequest('posts/index', false);

		$result = $this->auth->getUser($request, $this->response);
		$this->assertFalse($result);

		$expected = array(
			'id' => '1',
			'user' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => '2007-03-17 01:16:23',
			'updated' => '2007-03-17 01:18:31'
		);
		$_SERVER['HTTP_X_APITOKEN'] = '12345';
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);
	}

}
