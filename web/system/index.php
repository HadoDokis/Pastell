<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

if  (! $roleUtilisateur->hasDroit($authentification->getId(),'test:lecture',0)){
	header("Location: ".SITE_BASE . "/index.php");
	exit;
}

$page_title = "Environnement syst�me";

include( PASTELL_PATH ."/include/haut.php");


include (PASTELL_PATH."/include/bloc_message.php"); ?>


<div class="box_contenu clearfix">


<h2>Upstart</h2>
Dernier lancement du script action-automatique (par upstart ou crontab) : <?php echo $objectInstancier->LastUpstart->getLastMtime(); ?> 


<br/>
<br/>
<h2>V�rification de l'environnement</h2>
<a href='system/verif-environnement.php'>V�rifier l'environnement</a>
<br/><br/>


</div>



<?php 
include( PASTELL_PATH ."/include/bas.php");