<?php


class Action {
	
	private $action;
	
	public function __construct(array $action){
		$this->action = $action;
	}
	
	
	public function getAll(){
		return $this->action;
	}
	
}