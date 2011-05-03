<?php
require_once( dirname(__FILE__) . "/../init.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/document/DocumentEmail.class.php");
require_once( PASTELL_PATH . "/lib/journal/Journal.class.php");
require_once( PASTELL_PATH ."/lib/timestamp/OpensslTSWrapper.class.php");
require_once( PASTELL_PATH ."/lib/base/CurlWrapper.class.php");
require_once( PASTELL_PATH ."/lib/timestamp/SignServer.class.php");
require_once( PASTELL_PATH ."/lib/action/ActionCreator.class.php");
require_once( PASTELL_PATH . '/lib/formulaire/AfficheurFormulaire.class.php');
require_once( PASTELL_PATH . "/lib/document/DocumentEntite.class.php");

$recuperateur = new Recuperateur($_GET);
$key = $recuperateur->get('key');

$documentEmail = new DocumentEmail($sqlQuery);
$info  = $documentEmail->getInfoFromKey($key);

if (! $info ){
	header("Location: invalid.php");
	exit;
}


$documentEntite = new DocumentEntite($sqlQuery);

$id_e = $documentEntite->getEntiteWithRole($info['id_d'],'editeur');
$entite = new Entite($sqlQuery,$id_e);

$infoEntite = $entite->getInfo();

$documentType = $documentTypeFactory->getDocumentType('mailsec-destinataire');
$formulaire = $documentType->getFormulaire();
$donneesFormulaire = $donneesFormulaireFactory->get($info['id_d'],'mailsec-destinataire');
	$ip = $_SERVER['REMOTE_ADDR'];

if ($donneesFormulaire->get('password') && (! apc_fetch("consult_ok_{$key}_{$ip}"))){
	header("Location: password.php?key=$key");
	exit;
}
$info  = $documentEmail->consulter($key,$journal);

$afficheurFormulaire = new AfficheurFormulaire($formulaire,$donneesFormulaire);

$page= "Mail s�curis�";
$page_title= $infoEntite['denomination'] . " - Mail s�curis�";

include( PASTELL_PATH ."/include/haut.php");
?>
<?php 

$afficheurFormulaire->afficheTab(0,'');
?>
<div class="box_contenu"><?php 
$afficheurFormulaire->afficheStatic(0,"mailsec/recuperation-fichier.php?key=$key");
?>
</div>
<?php 
include( PASTELL_PATH ."/include/bas.php");
