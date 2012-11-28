<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$id_e = $recuperateur->getInt('id_e',0);
$id_d = $recuperateur->get('id_d');
$field = $recuperateur->get('field');

$document = $objectInstancier->Document;
$info = $document->getInfo($id_d);
if (!$info || ! $roleUtilisateur->hasDroit($id_u,"{$info['type']}:edition",$id_e)){
	$JSONoutput->displayErrorAndExit("Acces interdit id_e=$id_e, id_d=$id_d,id_u=$id_u");
}

$documentType = $documentTypeFactory->getDocumentType($info['type']);
$formulaire = $documentType->getFormulaire();

$theField = $formulaire->getField($field);

if ( ! $theField ){
	$JSONoutput->displayErrorAndExit("Type $field introuvable");
}

$script = $theField->getProperties('controler');

$name = "{$script}Controler";

require_once(PASTELL_PATH."/controler/{$script}Controler.class.php");

$controler = new $name($sqlQuery,$donneesFormulaireFactory,$formulaire);

$JSONoutput->display($controler->getData($id_e));