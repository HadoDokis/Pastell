<?php

require_once(dirname(__FILE__)."/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once (PASTELL_PATH . "/lib/action/DocumentActionEntite.class.php");
require_once( PASTELL_PATH . "/lib/helper/suivantPrecedent.php");
require_once( PASTELL_PATH . "/lib/document/DocumentListAfficheur.class.php");
require_once (PASTELL_PATH . "/lib/entite/NavigationEntite.class.php");


$recuperateur = new Recuperateur($_GET);
$id_e = $recuperateur->get('id_e',0);
$offset = $recuperateur->getInt('offset',0);
$limit = 20;


$documentActionEntite = new DocumentActionEntite($sqlQuery);

$liste_type = array();
$allDroit = $roleUtilisateur->getAllDroit($authentification->getId());



foreach($allDroit as $droit){
	if (preg_match('/^(.*):lecture$/',$droit,$result)){
		$liste_type[] = $result[1];
	}
}	



$liste_collectivite = $roleUtilisateur->getEntite($authentification->getId(),"entite:lecture");

if (! $id_e && count($liste_collectivite) == 1){
	$id_e = $liste_collectivite[0];
}

if ( ($id_e == 0) && (count($liste_collectivite) == 0)){
	header("Location: ".SITE_BASE."/nodroit.php");
	exit;
}


$entite = new Entite($sqlQuery,$id_e);
$infoEntite = $entite->getInfo();

$page_title= "Liste des documents <em>" . $infoEntite['denomination'] ."</em>";
include( PASTELL_PATH ."/include/haut.php");


if ($id_e != 0) {
	
	
	
	$listDocument = $documentActionEntite->getListDocumentByEntite($id_e,$liste_type,$offset,$limit);
	
	$count = $documentActionEntite->getNbDocumentByEntite($id_e,$liste_type);
	
	suivant_precedent($offset,$limit,$count,"document/index.php?id_e=$id_e");
	$documentListAfficheur = new DocumentListAfficheur($documentTypeFactory);
	
	$documentListAfficheur->affiche($listDocument,$id_e);

}




$navigationEntite = new NavigationEntite($id_e,$liste_collectivite);
$navigationEntite->affiche("document/index.php?a=a");

if ($id_e) : ?>
<a href='journal/index.php?id_e=<?php echo $id_e?>'>Voir le journal des évènements</a>
<br/><br/>
<?php 
endif;

include( PASTELL_PATH ."/include/bas.php");
