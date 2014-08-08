<?php
namespace FOC\Authenticate\Auth\Test\TestCase;

use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\TestSuite;

/**
 * All Authenticate plugin tests
 */
class AllAuthenticateTest extends TestCase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new TestSuite('All Authenticate test');

		$path = Plugin::path('FOC/Authenticate') . 'tests' . DS . 'TestCase' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}
}
