<a class='btn btn-mini' href='document/edition.php?id_d=<?php echo $id_d ?>&id_e=<?php echo $id_e?>&page=<?php echo $page ?>'><i class='icon-circle-arrow-left'></i>Revenir � l'�dition du document <em><?php echo $titre?></em></a>

<div class='box'>
<form action='document/external-data.php' method='get' >
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='page' value='<?php echo $page?>' />
	<input type='hidden' name='field' value='<?php echo $field?>' />
	
	<input type='text' name='search' value='<?php echo $search?>'/>
	<input type='submit' value='Rechercher' class='btn' />
</form>
</div>

<?php 
$this->SuivantPrecedent($offset,AgentSQL::NB_MAX,$nbAgent,"document/external-data.php?id_e=$id_e&id_d=$id_d&page=$page&field=$field");
?>

<div class="box">
<h2>Agent</h2>

<form action='document/external-data-controler.php' method='post'>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='page' value='<?php echo $page?>' />
	<input type='hidden' name='field' value='<?php echo $field?>' />

<table class="table table-striped">
	<tr>
		<th>&nbsp;</th>
		<th>Matricule</th>
		<th>Nom </th>
		<th>Pr�nom </th>
		<th>Statut</th>
		<th>Grade</th>
	</tr>
	<?php foreach ($listAgent as $i => $agent) : ?>
		<tr>
			<td class="w30">				
				<input type='radio' name='id_a' id="label_agent_<?php echo $i ?>" value='<?php echo $agent['id_a']?>'/></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent["matricule"] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['nom_patronymique'] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['prenom'] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent["emploi_grade_code"] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['emploi_grade_libelle'] ?></label></td>
			
		</tr>
	     
	<?php endforeach;?>
</table>

<input type='submit' value='Choisir' class='btn' />

</form>
</div>
