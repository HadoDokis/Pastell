<?php
abstract class SignatureConnecteur extends Connecteur {
	
		abstract public function getNbJourMaxInConnecteur();
		
		abstract public function getSousType();
	
		abstract public function getDossierID($id,$name);
		
		abstract function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array());
	
		abstract public function getHistorique($dossierID);
	
		abstract public function getSignature($dossierID);
	
		abstract public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf);
	
		abstract function getAllHistoriqueInfo($dossierID);
		
		abstract public function getLastHistorique($dossierID);
	
		abstract public function effacerDossierRejete($dossierID);
		
		public function hasTypeSousType(){
			return true;
		}	
		
		/**
		 * Indique si le connecteur est un connecteur de signature "locale", c'est � dire par applet sur le navigateur et sans appel � un serveur de signature externe
		 * @return boolean
		 */
		public function isLocalSignature(){
			return false;	
		}
}