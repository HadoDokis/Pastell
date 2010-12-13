<?php 
require_once(dirname(__FILE__)."/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/mailsec/Annuaire.class.php");
require_once( PASTELL_PATH . "/lib/helper/mail_validator.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->get('id_e');
$email = $recuperateur->get('email');

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

$annuaire = new Annuaire($sqlQuery,$id_e);

$annuaire->delete($email);

$lastMessage->setLastMessage("Email supprim� de la liste de contacts");
header("Location: annuaire.php?id_e=$id_e");