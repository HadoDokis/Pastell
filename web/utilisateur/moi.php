<?php 
require_once(dirname(__FILE__)."/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/entite/EntiteListe.class.php");
require_once( PASTELL_PATH . "/lib/notification/Notification.class.php");
require_once( PASTELL_PATH . "/lib/base/Certificat.class.php");
require_once (PASTELL_PATH . "/lib/helper/date.php");
require_once( PASTELL_PATH . "/lib/document/DocumentTypeHTML.class.php");

$id_u = $authentification->getId();

$utilisateur = new Utilisateur($sqlQuery,$id_u);
$documentTypeHTML = new DocumentTypeHTML();

$info = $utilisateur->getInfo();
if (! $info){
	$lastError->setLastError("Utilisateur $id_u inconnu");
	header("Location: ".SITE_BASE . "index.php");
}

$certificat = new Certificat($info['certificat']);


$roleDroit = new RoleDroit();

$page_title = "Espace utilisateur : ".$info['prenom']." " . $info['nom'];

$entiteListe = new EntiteListe($sqlQuery);

$tabEntite = $roleUtilisateur->getEntite($authentification->getId(),'entite:edition');


$notification = new Notification($sqlQuery);

$roleInfo =  $roleUtilisateur->getRole($id_u);


/*if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"utilisateur:lecture",$info['id_e'])) {
	$lastError->setLastError("Vous n'avez pas le droit de lecture (".$info['id_e'].")");
	header("Location: " . SITE_BASE . "index.php");
	exit;
}*/

$utilisateur_edition = $roleUtilisateur->hasDroit($authentification->getId(),"utilisateur:edition",$info['id_e']);


if( $info['id_e'] ){
	$entiteDeBase = new Entite($sqlQuery,$info['id_e']);
	$infoEntiteDeBase = $entiteDeBase->getInfo();
	$denominationEntiteDeBase = $infoEntiteDeBase['denomination'];
}

$arbre = $roleUtilisateur->getArbreFille($authentification->getId(),"entite:lecture");

include( PASTELL_PATH ."/include/haut.php");
 include (PASTELL_PATH."/include/bloc_message.php"); ?>

<div class="box_contenu clearfix">

<h2>Vos informations</h2>

<table class='tab_04'>

<tr>
<th>Login</th>
<td><?php echo $info['login'] ?></td>
</tr>

<tr>
<th>Pr�nom</th>
<td><?php echo $info['prenom'] ?></td>
</tr>

<tr>
<th>Nom</th>
<td><?php echo $info['nom'] ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $info['email'] ?></td>
</tr>

<tr>
<th>Date d'inscription</th>
<td><?php echo time_iso_to_fr($info['date_inscription']) ?></td>
</tr>


<tr>
<th>Entit� de base</th>
<td>
	<a href='entite/detail.php?id_e=<?php echo $info['id_e']?>'>
		<?php if ($info['id_e']) : ?>
			<?php echo $denominationEntiteDeBase ?>
		<?php else : ?>
			Utilisateur global
		<?php endif;?>
	</a> 
</td>
</tr>

<?php if ($certificat->isValid()) : ?>
<tr>
<th>Certificat</th>
<td><a href='utilisateur/certificat.php?verif_number=<?php echo $certificat->getVerifNumber() ?>'><?php echo $certificat->getFancy() ?></a></td>
</tr>
<?php endif;?>

</table>

<a href='utilisateur/modif-password.php' class='btn_maj'>Modifier mon mot de passe</a>

</div>

<div class="box_contenu clearfix">
<h2>Vos r�les sur Pastell : </h2>

<table class='tab_01'>
<tr>
<th>R�le</th>
<th>Entit�</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($roleUtilisateur->getRole($id_u) as $infoRole) : ?>
<tr>
	<td><?php echo $infoRole['role']?></td>
	<td>
		<?php if ($infoRole['id_e']) : ?>
			<a href='entite/detail.php?id_e=<?php echo $infoRole['id_e']?>'><?php echo $infoRole['denomination']?></a>
		<?php else : ?>
			Toutes les collectivit�s 
		<?php endif;?>
	</td> 
</tr>
<?php endforeach;?>
</table>

</div>

<div class="box_contenu clearfix">
<h2>Vos notifications</h2>
<table class='tab_02'>
<tr>
<th>Entit�</th>
<th>Type de document</th>
<th>Action</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($notification->getAll($id_u) as $infoNotification) : ?>
<tr>
	<td>
		<?php if ($infoNotification['id_e']) : ?>
			<a href='entite/detail.php?id_e=<?php echo $infoNotification['id_e']?>'><?php echo $infoNotification['denomination']?></a>
		<?php else : ?>
			Toutes les collectivit�s 
		<?php endif;?>
	</td> 
	<td>
		<?php if($infoNotification['type']): ?>
			<?php echo $documentTypeFactory->getDocumentType($infoNotification['type'])->getName() ?>
		<?php else : ?>
			Tous
		<?php endif; ?>
	</td>
	<td>
		<?php if ($infoNotification['action']) : ?>
			<?php echo $infoNotification['action'] ?>
		<?php else : ?>
			Toutes
		<?php endif;?>
	</td>
	
	<td>
		
			<a href='utilisateur/supprimer-notification.php?id_n=<?php echo $infoNotification['id_n'] ?>'>
				enlever cette notification
			</a>
	</td>
</tr>
<?php endforeach;?>
</table>
<form action='utilisateur/ajouter-notification.php' method='post'>
		<input type='hidden' name='id_u' value='<?php echo $id_u ?>' />
		
		<select name='id_e'>
			<option value=''>...</option>
			<?php foreach($arbre as $entiteInfo): ?>
			<option value='<?php echo $entiteInfo['id_e']?>'>
				<?php for($i=0; $i<$entiteInfo['profondeur']; $i++){ echo "&nbsp&nbsp;";}?>
				|_<?php echo $entiteInfo['denomination']?> </option>
			<?php endforeach ; ?>
		</select>
		<?php  $documentTypeHTML->displaySelectWithCollectivite($documentTypeFactory); ?>
			
		<input type='submit' value='ajouter'/>
	</form>
</div>
<?php 
include( PASTELL_PATH ."/include/bas.php");


