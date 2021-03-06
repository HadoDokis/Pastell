<?php
include( dirname(__FILE__) . "/../../init.php");
require_once( PASTELL_PATH . "/lib/Redirection.class.php");
require_once( PASTELL_PATH . "/lib/MailVerification.class.php");

$redirection = new Redirection("index.php");

$recuperateur = new Recuperateur($_POST);
$email = $recuperateur->get('email');
$password = $recuperateur->get('password');
$password2 = $recuperateur->get('password2');


if ( ! $email ){
	$lastError->setLastError("Il faut saisir un email");
	$redirection->redirect();
}

$entite = new Entite($sqlQuery,$email);
if ($entite->exists()){
	$lastError->setLastError("L'adresse que vous avez d�j� indiqu� est d�j� connu sur la plateforme");
	$redirection->redirect();
}


$id_u = $objectInstancier->UtilisateurCreator->create($email,$password,$password2,$email);

if ( ! $id_u){
	$lastError->setLastError($objectInstancier->UtilisateurCreator->getLastError());
	$redirection->redirect();
}

$utilisateur = new Utilisateur($sqlQuery);

$entiteCreator = new EntiteCreator($sqlQuery,$journal);
$id_e = $entiteCreator->edit(false,0,$email,Entite::TYPE_CITOYEN,0,0);

$roleUtilisateur->addRole($id_u,"citoyen",$id_e);

$infoUtilisateur = $utilisateur->getInfo($id_u);
$utilisateur->validMailAuto($id_u);

$redirection->redirect("inscription-ok.php");