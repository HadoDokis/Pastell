<a class='btn btn-mini' href='mailsec/groupe-list.php?id_e=<?php echo $id_e ?>'><i class='icon-circle-arrow-left'></i> Voir tous les groupes</a>

<br/><br/>
<div class="box">
<h2>Liste des contacts de �<?php echo $infoGroupe['nom']?>� </h2>

<?php $this->SuivantPrecedent($offset,AnnuaireGroupe::NB_MAX,$nbUtilisateur,"mailsec/groupe.php?id_e=$id_e&id_g=$id_g"); ?>



<form action='mailsec/del-contact-from-groupe.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />

<table  class="table table-striped">
	<tr>
	
		<th>Description</th>
		<th>Email</th>
		
	</tr>
<?php foreach($listUtilisateur as $utilisateur) : ?>
	<tr>
		<td><input type='checkbox' name='id_a[]' value='<?php echo $utilisateur['id_a'] ?>'/><?php echo $utilisateur['description']?></td>
		<td><?php echo $utilisateur['email']?></td>
	</tr>
<?php endforeach;?>
	
</table>
<?php if ($can_edit) : ?>
<input type='submit' value='Enlever du groupe' class='btn'/>
<?php endif; ?>

</form>
</div>

<?php if ( $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) : ?>

<div class="box">
<h2>Ajouter un contact � �<?php echo $infoGroupe['nom']?>� </h2>
<form action='mailsec/add-contact-to-groupe.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	
	<table class="table table-striped">
		<tbody>
			<tr>
				<th>Contact : </th>
				<td><input type='text' id='nom_contact' name='name' value='' /></td>
			</tr>	
		</tbody>
	</table>
	<script>
	 
 		 $(document).ready(function(){
				$("#nom_contact").pastellAutocomplete("mailsec/get-contact-ajax.php",<?php echo $id_e?>,true);

 		 });
	</script>
	<input type='submit' value='Ajouter' class='btn'/>
</form>
</div>
<?php endif;?>


<div class="box">
<h2>Partage</h2>

<?php if ($infoGroupe['partage']) : ?>
<div class='box_info'>
<p>Ce groupe est actuellement partag� avec les entit�s-filles (services, collectivit�s) de <?php  echo $infoEntite['denomination'] ?> qui peuvent l'utiliser 
pour leur propre mail.</p>
</div>
<form action='mailsec/partage-groupe.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	<input type='submit' value='Supprimer le partage' class='btn'/>
</form>
<?php else:?>
<div class='box_info'>
<p>Cliquer pour partager ce groupe avec les entit�s filles de <?php  echo $infoEntite['denomination'] ?>.</p>
</div>
<form action='mailsec/partage-groupe.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	<input type='submit' value='Partager' class='btn'/>
</form>
<?php endif;?>

</div>
