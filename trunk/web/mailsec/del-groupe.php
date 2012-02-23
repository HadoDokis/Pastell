<?php 
require_once(dirname(__FILE__)."/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/mailsec/AnnuaireGroupe.class.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->get('id_e');
$id_g = $recuperateur->get('id_g');

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

$annuaireGroupe = new AnnuaireGroupe($sqlQuery,$id_e);

$annuaireGroupe->delete($id_g);

$lastMessage->setLastMessage("Les groupes s�lectionn�s ont �t� supprim�s");
header("Location: groupe-list.php?id_e=$id_e");