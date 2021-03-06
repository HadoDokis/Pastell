<?php

abstract class ActionExecutor {
	
	protected $id_d;
	protected $id_e;
	protected $id_u;
	protected $action;
	protected $id_destinataire;
	protected $from_api;
	protected $id_ce;
	protected $type;
    protected $action_params;
	
	protected $objectInstancier;
	private $docDonneesFormulaire;
	private $docFormulaire;
	private $connecteurs;
        private $connecteurConfigs;
	
	private $lastMessage; 
	

	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
	}
	
	public function setEntiteId($id_e){
		$this->id_e = $id_e;	
	}
	
	public function setUtilisateurId($id_u){
		$this->id_u = $id_u;
	}
		
	public function setAction($action_name){
		$this->action = $action_name;
	}
		
	public function setConnecteurId($type, $id_ce){
		$this->id_ce = $id_ce;
		$this->type = $type;
	}
	
	public function setDocumentId($type, $id_d){
		$this->id_d = $id_d;
		$this->type = $type;
	}
	
	public function setDestinataireId(array $id_destinataire){
		$this->id_destinataire = $id_destinataire;	
	}
	
	public function setActionParams(array $action_params) {
		$this->action_params = $action_params;
	}
        
	public function setFromApi($from_api){
		$this->from_api = $from_api;
	}
	
	public function getLastMessage(){
		return $this->lastMessage;
	}
	
	public function setLastMessage($message){
		$this->lastMessage = $message;
	}
	
	/**
	 * @return ActionCreator
	 */
	public function getActionCreator($id_d = false){
		if (! $id_d){
			$id_d = $this->id_d;
		}
		return new ActionCreator($this->getSQLQuery(),$this->getJournal(),$id_d);	
	}
	
	/**
	 * @return DonneesFormulaire
	 */
	public function getDonneesFormulaire(){
		if (!$this->docDonneesFormulaire) {
			$this->docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        }
        return $this->docDonneesFormulaire;
	}
	
	/**
	 * Permet de r�cuperer l'objet Formulaire configur� pour ce DonneesFormulaire
	 * @return Formulaire
	 */
	public function getFormulaire(){
		return $this->docDonneesFormulaire->getFormulaire();
	}
	
	/**
	 * @return Journal
	 */
	public function getJournal(){
		return $this->objectInstancier->Journal;
	}
	
	/**
	 * @return ZenMail
	 */
	public function getZenMail(){
		return $this->objectInstancier->ZenMail;
	}
	
	/**
	 * @return DonneesFormulaireFactory
	 */
	public function getDonneesFormulaireFactory(){
		return $this->objectInstancier->DonneesFormulaireFactory;
	}
	
	/**
	 * @return DocumentEntite
	 */
	public function getDocumentEntite(){
		return $this->objectInstancier->DocumentEntite;
	}

	/**
	 * @return Document
	 */
	public function getDocument(){
		return $this->objectInstancier->Document;
	}
	
	/**
	 * @return DocumentActionEntite
	 */
	public function getDocumentActionEntite(){
		return $this->objectInstancier->DocumentActionEntite;
	}
	
	public function getDocumentTypeFactory(){
		return $this->objectInstancier->DocumentTypeFactory;
	}

	public function getEntite(){
		static $entite;
		if (empty($entite[$this->id_e])){
			$entite[$this->id_e] = new Entite($this->getSQLQuery(),$this->id_e);
		}
		return $entite[$this->id_e];
	}
	
	public function getSQLQuery(){
		return $this->objectInstancier->SQLQuery;
	}
	
	/**
	 * @return NotificationMail
	 */
	public function getNotificationMail(){
		return $this->objectInstancier->NotificationMail;
	}
	
	public function getActionName(){
		return $this->getDocumentTypeFactory()->getFluxDocumentType($this->type)->getAction()->getActionName($this->action);
	}
	
	
	/**** R�cup�ration de connecteur ****/
	/**
	 * @return DonneesFormulaire
	 */
	public function getConnecteurProperties(){
		assert('$this->id_ce');
		return $this->getConnecteurConfig($this->id_ce);
	}
	
	public function getGlobalConnecteur($type){
		return $this->objectInstancier->ConnecteurFactory->getGlobalConnecteur($type);
	}
	
	public function getMyConnecteur(){
		assert('$this->id_ce');
		return $this->objectInstancier->ConnecteurFactory->getConnecteurById($this->id_ce);
	}
	
	public function getConnecteur($type_connecteur){
		$connecteur = @$this->connecteurs[$type_connecteur];
		if (!$connecteur) {
			$connecteur = $this->objectInstancier->ConnecteurFactory->getConnecteurByType($this->id_e,$this->type,$type_connecteur);
			if (!$connecteur ){
				throw new Exception("Aucun connecteur $type_connecteur disponible");
			}
			$connecteur->setDocDonneesFormulaire($this->getDonneesFormulaire());
			$this->connecteurs[$type_connecteur] = $connecteur;
		}
		return $connecteur;
	}
	
	/**
	 * 
	 * @param string $type_connecteur
	 * @throws Exception
	 * @return DonneesFormulaire
	 */
	public function getConnecteurConfigByType($type_connecteur){
		$connecteurConfig = @$this->connecteurConfigs[$type_connecteur];
		if (!$connecteurConfig) {
			$connecteur_info = $this->objectInstancier->FluxEntiteSQL->getConnecteur($this->id_e,$this->type,$type_connecteur);
			if (! $connecteur_info){
				throw new Exception("Aucun connecteur $type_connecteur n'est d�fini pour le flux {$this->type}");
			}
			$connecteurConfig = $this->objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);		
			$this->connecteurConfigs[$type_connecteur] = $connecteurConfig;
		}
		return $connecteurConfig;
	}
	
	/**
	 * @return DonneesFormulaire
	 * @param unknown $id_ce
	 */
	public function getConnecteurConfig($id_ce){
		return $this->objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
	}
	

	
	/***** Fonction utilitaire *****/
	
	public function addActionOK($message = ""){
		$this->changeAction($this->action, $message);
	}
	
	public function changeAction($action,$message){
		$this->objectInstancier->ActionChange->addAction($this->id_d,$this->id_e,$this->id_u,$action,$message);
		$this->setLastMessage($message);
	}
	
	public function notify($actionName,$type,$message){
		$this->getNotificationMail()->notify($this->id_e,$this->id_d,$actionName,$type,$message);
	}

	public function redirect($to){
		if (! $this->from_api) {
			header("Location: ".SITE_BASE."$to");
			exit;
		}
	}
	
	public function checkIntf($object, $intf) {
		if (! ($object instanceof $intf)) {
			throw new Exception('L\'objet ' . get_class($object) . ' n\'impl�mente pas le contrat d\'interface ' . $intf);
		}
		return true;
	}

	abstract public function go();
	
	
	
}