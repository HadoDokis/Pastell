<?php

require_once( PASTELL_PATH . "/lib/document/DocumentDelete.class.php");
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");
 
class Supprimer extends ActionExecutor {

	public function go(){
		
		
		$document = $this->getDocument();
		$info = $document->getInfo($this->id_d);
		
		$type = $info['type'];
		
		$documentDelete = new DocumentDelete($this->getSQLQuery());
		$documentDelete->delete($this->id_d);
		
		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->delete();
		
		$journal = $this->getJournal();
		
		$journal->add(Journal::DOCUMENT_ACTION,$this->id_e,$this->id_d,"supression","Le document ".$this->id_d." � �t� supprim�");
		
		$this->setLastMessage("Document supprim�");


		header("Location: list.php?id_e=" . $this->id_e . "&type=$type");
		exit;
	}

}