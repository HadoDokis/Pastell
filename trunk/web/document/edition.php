<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");

require_once (PASTELL_PATH . "/lib/document/Document.class.php");
require_once (PASTELL_PATH . "/lib/document/DocumentType.class.php");

require_once (PASTELL_PATH . "/lib/formulaire/Formulaire.class.php");
require_once( PASTELL_PATH . "/lib/formulaire/DonneesFormulaire.class.php");
require_once( PASTELL_PATH . '/lib/formulaire/AfficheurFormulaire.class.php');


$recuperateur = new Recuperateur($_GET);
$id_d = $recuperateur->get('id_d');
$type = $recuperateur->get('type');
$id_e = $recuperateur->getInt('id_e');
$page = $recuperateur->getInt('page',0);

$document = new Document($sqlQuery);

if ($id_d){
	$info = $document->getInfo($id_d);
	$type = $info['type'];
} else {
	$info = array();
	$id_d = $document->getNewId();	
}


if ( ! $roleUtilisateur->hasDroit($authentification->getId(),$type.":edition",$id_e)) {
	header("Location: list.php");
	exit;
}

$documentType = new DocumentType(DOCUMENT_TYPE_PATH);
$formulaire = $documentType->getFormulaire($type);

$entite = new Entite($sqlQuery,$id_e);
$infoEntite = $entite->getInfo();
$page_title="Edition d'un document � " . $documentType->getName($type) . " � ( " . $infoEntite['denomination'] . " ) ";


$donneesFormulaire = new DonneesFormulaire( WORKSPACE_PATH . "/$id_d.yml");
$donneesFormulaire->setFormulaire($formulaire);

$afficheurFormulaire = new AfficheurFormulaire($formulaire,$donneesFormulaire);
$afficheurFormulaire->injectHiddenField("id_d",$id_d);
$afficheurFormulaire->injectHiddenField("form_type",$type);
$afficheurFormulaire->injectHiddenField("id_e",$id_e);

include( PASTELL_PATH ."/include/haut.php");
?>
<?php if ($info) : ?>
<a href='document/detail.php?id_d=<?php echo $id_d?>&id_e=<?php echo $id_e?>'>� <?php echo $info['titre'] ?></a>
<?php else : ?>
<a href='document/list.php?type=<?php echo $type ?>&id_e=<?php echo $id_e?>'>� Liste des documents <?php echo $documentType->getName($type);  ?></a>
<?php endif;?>
<br/><br/>

<div class="box_contenu clearfix">
<?php $afficheurFormulaire->affiche($page,"document/edition-controler.php","document/recuperation-fichier.php?id_d=$id_d"); ?>
</div>

<?php 
include( PASTELL_PATH ."/include/bas.php");
