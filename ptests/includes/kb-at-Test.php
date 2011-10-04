<?php

/**
 * Tests for KB_At
 *
 * @package PressTest
 * @author Kunal Bhalla
 * @version 0.1
 */

require PT_MOCK_DIR . '/core.php';

define( 'PLUGIN_DIRECTORY', dirname( dirname( dirname( __FILE__ ) ) ) );
require PLUGIN_DIRECTORY . '/includes/kb-at.php';

class testClass1 extends KB_At {
	/**
	 * @hook shutdown
	 */
	public function shouldHook() {
	}
}

class testClass2 extends KB_At {
	/**
	 * @hook hook2
	 * @priority 30
	 */
	public function shouldHookAndPrioritize() {
	}
}

class testClass3 extends KB_At {
	/**
	 * @hook hook3
	 * @priority 49
	 */
	public function checkArgCount( $a1, $a2, $a3 ) {
	}
}

class testClass4 extends KB_At {
	/**
	 * @hook h4
	 * @priority 20
	 */
	protected function noHook() {
	}

	/**
	 * @hook h5
	 */
	private function noHook2() {
	}

	public function noHook3() {
	}

	/**
	 * @priority 20
	 */
	public function noHook4() {
	}
}

class testClass5 extends KB_At {
	/**
	 * @hook shutdown
	 */
	public function shouldHook() {
	}

	/**
	 * @hook hook2
	 * @priority 30
	 */
	public function shouldHookAndPrioritize() {
	}

	/**
	 * @hook hook3
	 * @priority 49
	 */
	public function checkArgCount( $a1, $a2, $a3 ) {
	}

	/**
	 * @hook h4
	 * @priority 20
	 */
	protected function noHook() {
	}

	/**
	 * @hook h5
	 */
	private function noHook2() {
	}

	public function noHook3() {
	}

	/**
	 * @priority 20
	 */
	public function noHook4() {
	}
}

class testClass6 extends testClass5 {
}


/**
 * Checks the doc-block parser and 
 * adding hooks.
 */ 
class KB_At_Test extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		PT_Mime::clear();
	}

	public function testSimple() {
		$simple = new testClass1();
		$calls = PT_Mime::fget_calls( 'add_filter' );

		$this->assertEquals( count( $calls ), 1 );
		$this->assertEquals( $calls[0], Array( 'shutdown', Array( $simple, 'shouldHook'), 10, 0) );
	}

	public function testPriority() {
		$priority = new testClass2();
		$calls = PT_Mime::fget_calls( 'add_filter' );

		$this->assertEquals( count( $calls ), 1 );
		$this->assertEquals( $calls[0], Array( 'hook2', Array( $priority, 'shouldHookAndPrioritize' ), 30, 0 ) );
	}

	public function testArguments() {
		$args = new testClass3();
		$calls = PT_Mime::fget_calls( 'add_filter' );

		$this->assertEquals( count( $calls ), 1 );
		$this->assertEquals( $calls[0], Array( 'hook3', Array( $args, 'checkArgCount' ), 49, 3 ) );
	}

	public function testIgnore() {
		$hookless = new testClass4();
		$calls = PT_Mime::fget_calls( 'add_filter' );
		
		$this->assertEmpty( $calls );
	}

	public function testMixed() {
		$mixed = new testClass5();
		$calls = PT_Mime::fget_calls( 'add_filter' );

		$this->assertEquals( count( $calls ), 3);
		$this->assertEquals( $calls, Array(
			Array( 'shutdown', Array( $mixed, 'shouldHook'), 10, 0),
			Array( 'hook2', Array( $mixed, 'shouldHookAndPrioritize' ), 30, 0 ),
			Array( 'hook3', Array( $mixed, 'checkArgCount' ), 49, 3 )
		) );
	}

	public function testInherit() {
		$mixed = new testClass6();
		$calls = PT_Mime::fget_calls( 'add_filter' );

		$this->assertEquals( count( $calls ), 3);
		$this->assertEquals( $calls, Array(
			Array( 'shutdown', Array( $mixed, 'shouldHook'), 10, 0),
			Array( 'hook2', Array( $mixed, 'shouldHookAndPrioritize' ), 30, 0 ),
			Array( 'hook3', Array( $mixed, 'checkArgCount' ), 49, 3 )
		) );
	}
}
