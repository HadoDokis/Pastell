<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_REQUEST);
$id_ce = $recuperateur->get('id_ce');
$field = $recuperateur->get('field');


$connecteur_info = $objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
$id_e  = $connecteur_info['id_e'];

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"entite:edition",$id_e)) {
	$lastError->setLastError("Vous n'avez pas le droit de faire cette action (entite:edition)");
	header("Location: edition-properties.php?id_e=$id_e&page=$page");
	exit;
}

$documentType = $documentTypeFactory->getEntiteDocumentType($connecteur_info['id_connecteur']);
$formulaire = $documentType->getFormulaire();
$theField = $formulaire->getField($field);


$action_name = $theField->getProperties('choice-action');
if ($action_name) {
	$result = $objectInstancier->ActionExecutorFactory->goChoiceOnConnecteur($id_ce,$authentification->getId(),$action_name,$field);	
} else {
	$script = $theField->getProperties('script-controler');
	require_once(PASTELL_PATH . "/externaldata/$script");
}
