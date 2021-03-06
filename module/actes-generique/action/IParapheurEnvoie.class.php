<?php


class IParapheurEnvoie extends ActionExecutor {
	
	public function go(){
		
		$signature = $this->getConnecteur('signature');
		
		$actes = $this->getDonneesFormulaire();
		
		$file_content = file_get_contents($actes->getFilePath('arrete'));
		$finfo = new finfo(FILEINFO_MIME);
		$content_type = $finfo->file($actes->getFilePath('arrete'),FILEINFO_MIME_TYPE);
		
		$annexe = array();
		if ($actes->get('autre_document_attache')) {
			foreach($actes->get('autre_document_attache') as $num => $fileName ){
				$annexe_content =  file_get_contents($actes->getFilePath('autre_document_attache',$num));
				$annexe_content_type = $finfo->file($actes->getFilePath('autre_document_attache',$num),FILEINFO_MIME_TYPE);
					
				$annexe[] = array(
					'name' => $fileName,
					'file_content' => $annexe_content,
					'content_type' => $annexe_content_type,
				);
				
			}
		}
		
		$dossierID = $signature->getDossierID($actes->get('numero_de_lacte'),$actes->get('objet'));
		$result = $signature->sendDocument($actes->get('iparapheur_type'),
											$actes->get('iparapheur_sous_type'),
											$dossierID,
											$file_content,
											$content_type,$annexe);				
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $signature->getLastError());
			return false;
		}
		
		$this->addActionOK("Le document a �t� envoy� au parapheur �lectronique");
		$this->notify($this->action, $this->type,"Le document a �t� envoy� au parapheur �lectronique");
		
		return true;
	}
	
}