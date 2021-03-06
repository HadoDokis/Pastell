<?php
require_once(dirname(__FILE__)."/../init.php");

$JSONoutput = new JSONoutput();

$recuperateur = new Recuperateur($_REQUEST);
$auth = $recuperateur->get("auth");

$id_u = false;

if ($auth=='cas') {
	try{
		$id_u = $objectInstancier->ConnexionControler->apiCasConnexion();
	} catch(Exception $e){
		$JSONoutput->displayErrorAndExit($e->getMessage());
		exit;
	}
}

if (!$id_u){
	$certificatConnexion = new CertificatConnexion($sqlQuery);
	$id_u = $certificatConnexion->autoConnect();
}
	
$utilisateur = new Utilisateur($sqlQuery);
if ( ! $id_u && ! empty($_SERVER['PHP_AUTH_USER'])){
	$utilisateurListe = new UtilisateurListe($sqlQuery);
	$id_u = $utilisateurListe->getUtilisateurByLogin($_SERVER['PHP_AUTH_USER']);
	$utilisateur = new Utilisateur($sqlQuery);

	if ( ! $utilisateur->verifPassword($id_u,$_SERVER['PHP_AUTH_PW']) ){
		$id_u = false;
	}
}

if (! $id_u){
	header('HTTP/1.1 401 Unauthorized');
	header('WWW-Authenticate: Basic realm="API Pastell"');
	$JSONoutput->displayErrorAndExit("Acces interdit");
}

$info_utilisateur = $utilisateur->getInfo($id_u);
$objectInstancier->Authentification->connexion($info_utilisateur['login'],$id_u);

$apiAction = new APIAction($objectInstancier,$id_u);
$api_json = new API_JSON($apiAction,$JSONoutput);
