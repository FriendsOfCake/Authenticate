<?php
namespace FOC\Authenticate\Auth\Test\TestCase\Auth;

use Cake\Controller\Component\AuthComponent;
use Cake\Datasource\ConnectionManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\I18n\Time;
use FOC\Authenticate\Auth\MultiColumnAuthenticate;

/**
 * Test case for FormAuthentication
 */
class MultiColumnAuthenticateTest extends TestCase {

	public $fixtures = ['plugin.FOC\Authenticate.multi_users'];

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Registry = $this->getMock('Cake\Controller\ComponentRegistry');
		$this->auth = new MultiColumnAuthenticate($this->Registry, [
			'fields' => ['username' => 'user_name', 'password' => 'password'],
			'userModel' => 'MultiUsers',
			'columns' => ['user_name', 'email']
		]);

		$password = password_hash('password', PASSWORD_DEFAULT);
		$MultiUsers = TableRegistry::get('MultiUsers');
		$MultiUsers->updateAll(['password' => $password], []);

		$this->response = $this->getMock('Cake\Network\Response');
	}

/**
 * test authenticate email or username
 *
 * @return void
 */
	public function testAuthenticateEmailOrUsername() {
		$request = new Request('posts/index');
		$expected = array(
			'id' => 1,
			'user_name' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => new Time('2007-03-17 01:16:23'),
			'updated' => new Time('2007-03-17 01:18:31')
		);

		$request->data = [
			'user_name' => 'mariano',
			'password' => 'password'
		];
		$result = $this->auth->authenticate($request, $this->response);
		$this->assertEquals($expected, $result);

		$request->data = [
			'user_name' => 'mariano@example.com',
			'password' => 'password'
		];
		$result = $this->auth->authenticate($request, $this->response);
		$this->assertEquals($expected, $result);
	}

/**
 * test the authenticate method
 *
 * @return void
 */
	public function testAuthenticateNoData() {
		$request = new Request('posts/index');
		$request->data = [];
		$this->assertFalse($this->auth->authenticate($request, $this->response));
	}

/**
 * test the authenticate method
 *
 * @return void
 */
	public function testAuthenticateNoUsername() {
		$request = new Request('posts/index');
		$request->data = ['password' => 'foobar'];
		$this->assertFalse($this->auth->authenticate($request, $this->response));
	}

/**
 * test the authenticate method
 *
 * @return void
 */
	public function testAuthenticateNoPassword() {
		$request = new Request('posts/index');
		$request->data = ['user_name' => 'mariano'];
		$this->assertFalse($this->auth->authenticate($request, $this->response));

		$request->data = ['user_name' => 'mariano@example.com'];
		$this->assertFalse($this->auth->authenticate($request, $this->response));
	}

/**
 * test the authenticate method
 *
 * @return void
 */
	public function testAuthenticateInjection() {
		$request = new Request('posts/index');
		$request->data = [
			'user_name' => '> 1',
			'password' => "' OR 1 = 1"
		];
		$this->assertFalse($this->auth->authenticate($request, $this->response));
	}

/**
 * test scope failure.
 *
 * @return void
 */
	public function testAuthenticateScopeFail() {
		$this->auth->config('scope', ['user_name' => 'nate']);
		$request = new Request('posts/index');
		$request->data = [
			'user_name' => 'mariano',
			'password' => 'password'
		];

		$this->assertFalse($this->auth->authenticate($request, $this->response));
	}

}
