<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$nom = $recuperateur->get('nom');


if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}


$annuaireGroupe = new AnnuaireGroupe($sqlQuery,$id_e);

$annuaireGroupe->add($nom);

$lastMessage->setLastMessage("Le groupe � $nom � a �t� cr��");
header("Location: groupe-list.php?id_e=$id_e");