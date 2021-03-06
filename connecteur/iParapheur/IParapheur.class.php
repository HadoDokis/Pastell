<?php 

class IParapheur extends SignatureConnecteur {
	
	const IPARAPHEUR_NB_JOUR_MAX_DEFAULT = 30;
	
	private $wsdl;
	private $userCert;
	private $userCertPassword;
	private $login_http;
	private $password_http;
	
	private $userKeyOnly;
	private $userCertOnly;
	
	private $iparapheur_type;
	private $iparapheur_nb_jour_max;
	private $visibilite;
	private $xPathPourSignatureXML;
	
	private $soapClientFactory;
	
	public function __construct(SoapClientFactory $soapClientFactory){
		$this->soapClientFactory = $soapClientFactory;
	}
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->wsdl = $collectiviteProperties->get("iparapheur_wsdl");
		$this->activate = $collectiviteProperties->get("iparapheur_activate");
		$this->userCert = $collectiviteProperties->getFilePath("iparapheur_user_key_pem");
		$this->userCertPassword = $collectiviteProperties->get("iparapheur_user_certificat_password");
		$this->login_http = $collectiviteProperties->get("iparapheur_login");
		$this->password_http = $collectiviteProperties->get("iparapheur_password");
		
		$this->userKeyOnly = $collectiviteProperties->getFilePath("iparapheur_user_key_only_pem");
		$this->userCertOnly = $collectiviteProperties->getFilePath("iparapheur_user_certificat_pem");
		$this->iparapheur_type = $collectiviteProperties->get("iparapheur_type");
		$this->iparapheur_nb_jour_max = $collectiviteProperties->get("iparapheur_nb_jour_max");
		
		$this->visibilite = $collectiviteProperties->get('iparapheur_visibilite')?:"SERVICE";
		
		$this->xPathPourSignatureXML =  $collectiviteProperties->get('XPathPourSignatureXML');
	}
	
	public function getNbJourMaxInConnecteur(){		
		if ($this->iparapheur_nb_jour_max){
			return $this->iparapheur_nb_jour_max;
		}
		return self::IPARAPHEUR_NB_JOUR_MAX_DEFAULT;
	}
	
	
	public function getDossierID($id,$name){
		$name = preg_replace("#[^a-zA-Z0-9_ ]#", "_", $name);
		return "$id $name";
	}
	
	public function getDossier($dossierID){
		return  $this->getClient()->GetDossier(utf8_encode($dossierID));
	}
	
	public function getBordereau($result){
		$info = array();
		if (! isset($result->DocumentsAnnexes)){
			$info['document'] = false;
			$info['nom_document'] = false;
			return $info;
		}
		
		if (isset($result->DocumentsAnnexes->DocAnnexe->fichier)){
			$info['document'] = $result->DocumentsAnnexes->DocAnnexe->fichier->_;
			$info['nom_document'] = $result->DocumentsAnnexes->DocAnnexe->nom;
			return $info;
		} 
		
		foreach($result->DocumentsAnnexes->DocAnnexe as $bordereau){}
		$info['document'] = $bordereau->fichier->_;
		$info['nom_document'] = $bordereau->nom;
		return $info;
	}
	
	private function getDocumentSigne($result){
		$info = array();
		if (! isset($result->DocPrincipal)){
			$info['document'] = false;
			$info['nom_document'] = false;
			return $info;
		}
		$info['document'] = $result->DocPrincipal->_;
		$info['nom_document'] = $result->NomDocPrincipal;
		return $info;
	}
	
	public function getSignature($dossierID){
		try{
			$result =  $this->getClient()->GetDossier(utf8_encode($dossierID));
			if ($result->MessageRetour->codeRetour != 'OK'){
				$message = "[{$result->MessageRetour->severite}] {$result->MessageRetour->message}";
				$this->lastError = utf8_decode($message);
				return false;
			}
			$info = $this->getBordereau($result);
			
			if (isset($result->SignatureDocPrincipal)){
				$info['signature'] = $result->SignatureDocPrincipal->_;
			} elseif (isset($result->FichierPES)) {
				$info['signature'] = $result->FichierPES->_;
			} else {
				$info['signature'] = false;
			}
			
			$info['document_signe'] = $this->getDocumentSigne($result);
			
			$this->archiver($dossierID);
			return $info;
		} catch (Exception $e){
		 	$this->lastError = "Erreur sur la r�cuperation de la signature : ".$e->getMessage();
			return false;			
		}
	}
	
	public function archiver($dossierID){
		try {
			$result = $this->getClient()->ArchiverDossier(array("DossierID" => utf8_encode($dossierID),"ArchivageAction"=>"EFFACER"));
		} catch(Exception $e){
			$this->lastError = $e->getMessage();
			return false;
		}
		return $result;
	}
	
	public function effacerDossierRejete($dossierID){
		try {
			$result = $this->getClient()->EffacerDossierRejete(utf8_encode($dossierID));
		} catch(Exception $e){
			$this->lastError = $e->getMessage();
			return false;
		}
		return $result;
	}
	
	public function getAllHistoriqueInfo($dossierID){
		try{
			$result =  $this->getClient()->GetHistoDossier(utf8_encode($dossierID));
			if ( empty($result->LogDossier)){
				$this->lastError = "Le dossier n'a pas �t� trouv�";
				return false;
			}
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function getLastHistorique($all_historique){
		$lastLog = end($all_historique->LogDossier);
		$date = date("d/m/Y H:i:s",strtotime($lastLog->timestamp));
		return utf8_decode($date . " : [" . $lastLog->status . "] ".$lastLog->annotation);
	}
	
	public function getHistorique($dossierID){
		try{
			$result =  $this->getClient()->GetHistoDossier(utf8_encode($dossierID));
			
			if ( empty($result->LogDossier)){
				$this->lastError = "Le dossier n'a pas �t� trouv�";
				return false;
			}
			return $this->getLastHistorique($result);
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf){
			
		try {
			$client = $this->getClient();	
			$data = array(
					"TypeTechnique"=>utf8_encode($typeTechnique),
					"SousType"=> utf8_encode($sousType),
					"DossierID" => utf8_encode($dossierID),
					"DocumentPrincipal" => array("_"=>$document_content,"contentType"=>$content_type),
					"VisuelPDF" => array("_" => $visuel_pdf, "contentType" => "application/pdf"),
					"Visibilite" => $this->visibilite,
					"XPathPourSignatureXML" => $this->getXPathPourSignatureXML($document_content),
					
			); 
			
			$result =  $client->CreerDossier($data);

			$messageRetour = $result->MessageRetour;
			$message = "[{$messageRetour->severite}] {$messageRetour->message}";
			if ($messageRetour->codeRetour == "KO"){
				$this->lastError = utf8_decode($message);
				return false;
			} elseif($messageRetour->codeRetour == "OK") {
				return utf8_decode($message);
			} else {
				$this->lastError = "Le iparapheur n'a pas retourn� de code de retour : " . utf8_decode($message);
				return false;
			}		
		} catch (Exception $e){
			$this->lastError = $e->getMessage() ;
			if (! empty($client)){
				$this->lastError .= $client->__getLastResponse();
			} 
			return false;			
		}
		
	}
	
	
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,
			array $all_annexes = array(),
			$date_limite = false
			){
// 		echo '<pre>';
// var_dump($typeTechnique); 
// var_dump($sousType);
// var_dump($dossierID);
// echo '</pre>';
// die();
		try {
			$client = $this->getClient();		
			
			$data = array(
						"TypeTechnique"=>utf8_encode($typeTechnique),
						"SousType"=> utf8_encode($sousType),
						"DossierID" => utf8_encode($dossierID),
						"DocumentPrincipal" => array("_"=>$document_content,"contentType"=>$content_type),
						"Visibilite" => $this->visibilite,
						
				); 
			
			if ($date_limite) {
				$data['DateLimite'] = $date_limite;
			}
			if ($all_annexes){
				$data["DocumentsAnnexes"] = array();
			}
			foreach($all_annexes as $annexe){
					$data["DocumentsAnnexes"][] = array("nom"=>utf8_encode($annexe['name']),
													"fichier" => array("_"=>$annexe['file_content'],
													"contentType"=>$annexe['content_type']),
													"mimetype" => $annexe['content_type'],
													"encoding" => "UTF-8"
				);
				
			}
			$result =  $client->CreerDossier($data);

			$messageRetour = $result->MessageRetour;
			$message = "[{$messageRetour->severite}] {$messageRetour->message}";
			if ($messageRetour->codeRetour == "KO"){
				$this->lastError = utf8_decode($message);
				return false;
			} elseif($messageRetour->codeRetour == "OK") {
				return utf8_decode($message);
			} else {
				$this->lastError = "Le iparapheur n'a pas retourn� de code de retour : " . utf8_decode($message);
				return false;
			}		
		} catch (Exception $e){
			$this->lastError = $e->getMessage() ;
			if (! empty($client)){
				$this->lastError .= $client->__getLastResponse();
			} 
			return false;			
		}
		
	}
	
	public function sendDocumentTest(){
		$dossierID = mt_rand();
		$document = file_get_contents(PASTELL_PATH . "/data-exemple/exemple.pdf");		
		return $this->sendDocument("Actes","Deliberation",$dossierID,$document,"application/pdf");
	}
	
	protected function getClient(){
// 		static $client;
// var_dump($client);
// 		if ($client) {
// 			return $client;
// 		}
		if ( ! $this->activate){
			$this->lastError = "Le module n'est pas activ�";
			throw new Exception("Le module n'est pas activ�");
		}
		if (! $this->wsdl ){
			$this->lastError = "Le WSDL n'a pas �t� fourni";
			throw new Exception("Le WSDL n'a pas �t� fourni");
		}
		
		
		$client = $this->soapClientFactory->getInstance(
				$this->wsdl,
				array(
	     			'local_cert' => $this->userCert,
	     			'passphrase' => $this->userCertPassword,
					'login' => $this->login_http,
					'password' => $this->password_http,
					'trace' => 1,
					'exceptions' => 1,
					'use_curl' => 1,
					'userKeyOnly' => $this->userKeyOnly,
					'userCertOnly' => $this->userCertOnly,
	    		),true);

// echo '<pre>';
// var_dump($client);
// // die();
		return $client;
	} 
	
	public function getType(){
		try{
			$type = $this->getClient()->GetListeTypes()->TypeTechnique;			
			if (is_array($type)){
				foreach($type as $n => $v){
					$result[$n] = utf8_decode($v);
				}
			} else {
				$result[0] = utf8_decode($type);
			}
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function getSousType(){
		$type = $this->iparapheur_type;
		try{
			$sousType = $this->getClient()->GetListeSousTypes($type)->SousType;
			$result = array();
			if (is_array($sousType)){
				foreach($sousType as $n => $v){
					$result[$n] = utf8_decode($v);
				}
			} else {
				$result[0] = utf8_decode($sousType);
			}
			return $result;
		}  catch (Exception $e){
			$this->lastError = $e->getMessage();
			return false;			
		}
	}
	
	public function testConnexion(){
		$client = $this->getClient();
		return $client->echo("test_connexion_pastell");
	}
	
	public function getLogin(){
		return $this->login_http;
	}
	
	public function getXPathPourSignatureXML($pes_content){
		if ($this->xPathPourSignatureXML == 2){
			return "//Bordereau";
		}
		if ($this->xPathPourSignatureXML == 3){
			return ".";
		}
		return $this->getXPathPourSignatureXMLBestMethod($pes_content);
	}
	
	public function getXPathPourSignatureXMLBestMethod($pes_content){
		$xml = simplexml_load_string($pes_content);
	
		if ($this->allBordereauHasId($xml)){
			return "//Bordereau";
		}
		if (! empty($xml['Id'])){
			return ".";			
		}
		
		throw new Exception("Le bordereau du fichier PES ne contient pas d'identifiant valide, ni la balise PESAller : signature impossible");
	}

	private function allBordereauHasId($simple_xml_pes_content){
		if ($simple_xml_pes_content->PES_DepenseAller){
			$root = $simple_xml_pes_content->PES_DepenseAller;
		} else if($simple_xml_pes_content->PES_RecetteAller) {
			$root = $simple_xml_pes_content->PES_RecetteAller;
		} else {
			throw new Exception("Le bordereau ne contient ni Depense ni Recette");
		}
	
		foreach($root->Bordereau as $bordereau){
			$attr = $bordereau->attributes();
			if (empty($attr['Id'])){
				return false;
			}
		}
		return true;
	}
	
	
}
