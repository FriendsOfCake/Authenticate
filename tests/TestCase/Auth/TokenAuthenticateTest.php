<?php
namespace FOC\Authenticate\Auth\Test\TestCase\Auth;

use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Controller;
use Cake\I18n\Time;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use FOC\Authenticate\Auth\TokenAuthenticate;

/**
 * Test case for FormAuthentication
 */
class TokenAuthenticateTest extends TestCase {

	public $fixtures = ['plugin.FOC\Authenticate.multi_users'];

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Registry = $this->getMock('Cake\Controller\ComponentRegistry');
		$this->auth = new TokenAuthenticate($this->Registry, [
			'fields' => [
				'username' => 'user_name',
				'password' => 'password',
				'token' => 'token'
			],
			'userModel' => 'MultiUsers'
		]);

		$password = password_hash('password', PASSWORD_DEFAULT);
		$MultiUsers = TableRegistry::get('MultiUsers');
		$MultiUsers->updateAll(['password' => $password], []);

		$this->response = $this->getMock('Cake\Network\Response');
	}

/**
 * test authenticate token as query parameter
 *
 * @return void
 */
	public function testAuthenticateTokenParameter() {
		$this->auth->config('_parameter', 'token');
		$request = new Request('posts/index?_token=54321');

		$result = $this->auth->getUser($request, $this->response);
		$this->assertFalse($result);

		$expected = array(
			'id' => 1,
			'user_name' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => new Time('2007-03-17 01:16:23'),
			'updated' => new Time('2007-03-17 01:18:31')
		);
		$request = new Request('posts/index?_token=12345');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);

		$this->auth->config('parameter', 'tokenname');
		$request = new Request('posts/index?tokenname=12345');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);
	}

/**
 * test authenticate token as request header
 *
 * @return void
 */
	public function testAuthenticateTokenHeader() {
		$request = new Request('posts/index');

		$expected = array(
			'id' => 1,
			'user_name' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => new Time('2007-03-17 01:16:23'),
			'updated' => new Time('2007-03-17 01:18:31')
		);
		$request->env('HTTP_X_APITOKEN', '12345');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertEquals($expected, $result);

		$request->env('HTTP_X_APITOKEN', '66666');
		$result = $this->auth->getUser($request, $this->response);
		$this->assertFalse($result);
	}

}
