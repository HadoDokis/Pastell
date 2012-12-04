<?php
require_once( __DIR__ . "/../Megalis.class.php");

class MegalisTestSSH extends ActionExecutor {
	
	public function go(){
		$megalis = new Megalis($this->getConnecteurProperties(),new SSH2());
		$directory_listing = $megalis->listDirectory();
		if (! $directory_listing ){
			$this->setLastMessage($megalis->getLastError());
			return false;	
		}
		$this->setLastMessage("Connexion SSH OK. <br/>Contenu du r�pertoire : ".implode(", ",$directory_listing));
		return true;
	}
	
}