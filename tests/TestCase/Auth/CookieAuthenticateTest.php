<?php
namespace FOC\Authenticate\Auth\Test\TestCase\Auth;

use Cake\Auth\BasicAuthenticate;
use Cake\Controller\Component\ComponentRegistry;
use Cake\Controller\Component\CookieComponent;
use Cake\Controller\Component\SessionComponent;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use FOC\Authenticate\Auth\CookieAuthenticate;

/**
 * Test case for FormAuthentication
 *
 * @package       Cake.Test.Case.Controller.Component.Auth
 */
class CookieAuthenticateTest extends TestCase {

	public $fixtures = array('plugin.authenticate.multi_user');

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->request = new CakeRequest('posts/index', false);
		Router::setRequestInfo($this->request);
		$this->Collection = new ComponentCollection();
		$this->Collection->load('Cookie');
		$this->Collection->load('Session');
		$this->auth = new CookieAuthenticate($this->Collection, array(
			'fields' => array('username' => 'user', 'password' => 'password'),
			'userModel' => 'MultiUser',
		));
		$password = Security::hash('password', null, true);
		$User = ClassRegistry::init('MultiUser');
		$User->updateAll(array('password' => $User->getDataSource()->value($password)));
		$this->response = $this->getMock('CakeResponse');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$this->Collection->Cookie->destroy();
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
			'created' => '2007-03-17 01:16:23',
			'updated' => '2007-03-17 01:18:31'
		);

		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertFalse($result);

		$this->Collection->Cookie->write('MultiUser', array('user' => 'mariano', 'password' => 'password'));
		$result = $this->auth->authenticate($this->request, $this->response);
		$this->assertEquals($expected, $result);
	}
}
