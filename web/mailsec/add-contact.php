<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$description = $recuperateur->get('description',"");
$email = $recuperateur->get('email');


if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
	$lastError->setLastError("$email ne semble pas �tre un email valide");
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

$annuaire = new Annuaire($sqlQuery,$id_e);

if($annuaire->getFromEmail($email)){
	$lastError->setLastError("$email existe d�j� dans l'annuaire");
	header("Location: annuaire.php?id_e=$id_e");
	exit;	
}

$annuaire->add($description,$email);

$mail = htmlentities("\"$description\"<$email>",ENT_QUOTES);

$lastMessage->setLastMessage("$mail a �t� ajout� � la liste de contacts");
header("Location: annuaire.php?id_e=$id_e");