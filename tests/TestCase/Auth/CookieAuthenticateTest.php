<?php
namespace FOC\Authenticate\Auth\Test\TestCase\Auth;

use Cake\Auth\BasicAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\Utility\Time;
use FOC\Authenticate\Auth\CookieAuthenticate;

/**
 * Test case for FormAuthentication
 */
class CookieAuthenticateTest extends TestCase {

	public $fixtures = ['plugin.FOC\Authenticate.multi_user'];

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->request = new Request('posts/index');
		Router::setRequestInfo($this->request);
		$this->response = $this->getMock('Cake\Network\Response');

		Configure::write('Security.salt', 'somerandomhaskeysomerandomhaskey');
		$this->Registry = new ComponentRegistry(new Controller($this->request, $this->response));
		$this->Registry->load('Cookie');
		$this->Registry->load('Session');
		$this->Registry->load('Auth');
		$this->auth = new CookieAuthenticate($this->Registry, [
			'fields' => ['username' => 'user', 'password' => 'password'],
			'userModel' => 'MultiUsers',
		]);

		$password = password_hash('password', PASSWORD_DEFAULT);
		$MultiUsers = TableRegistry::get('MultiUsers');
		$MultiUsers->updateAll(['password' => $password], []);
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$this->Registry->Cookie->delete('MultiUsers');
	}

/**
 * test authenticate email or username
 *
 * @return void
 */
	public function testAuthenticate() {
		$expected = array(
			'id' => 1,
			'user' => 'mariano',
			'email' => 'mariano@example.com',
			'token' => '12345',
			'created' => new Time('2007-03-17 01:16:23'),
			'updated' => new Time('2007-03-17 01:18:31')
		);

		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertFalse($result);

		$this->Registry->Cookie->write(
			'RememberMe',
			array('user' => 'mariano', 'password' => 'password')
		);
		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertEquals($expected, $result);
	}

}
