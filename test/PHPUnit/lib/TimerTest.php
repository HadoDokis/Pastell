<?php

require_once __DIR__.'/../init.php';

class TimerTest extends PHPUnit_Framework_TestCase {
	
	public function testAll(){
		$timer = new Timer();
		$this->assertTrue($timer->getElapsedTime() > 0);
	}
}