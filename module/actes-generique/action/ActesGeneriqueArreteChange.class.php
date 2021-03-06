<?php
class ActesGeneriqueArreteChange extends ActionExecutor{

	public function go(){
		$content_type = $this->getDonneesFormulaire()->getContentType('arrete');
		//V�rifier que le doc est en xml ou en pdf
		if (in_array($content_type,array("application/pdf","application/xml"))){
			return true;
		}
		
		$filename = $this->getDonneesFormulaire()->getFileName('arrete');
		
		if (! in_array($content_type,array("application/vnd.oasis.opendocument.text",	
											"application/vnd.ms-office",
											"application/vnd.openxmlformats-officedocument.wordprocessingml.document")
			)){
				throw new Exception("Le document $filename est au format $content_type ! Or, il doit �tre au format PDF ou XML. Il sera bloqu� par le tiers de t�l�transmission");
		}

		$pdfConverter = $this->getGlobalConnecteur('convertisseur-office-pdf');
		if (! $pdfConverter){
			throw new Exception("Le document � $filename � n'est pas dans le bon format et aucun convertisseur PDF n'est configur�.");
		}

		$pdfConverter->convertField($this->getDonneesFormulaire(),'arrete','arrete');
		$this->setLastMessage("Le document � $filename � a �t� converti au format PDF pour respecter la norme ACTES");
		return true;
	}
	
}