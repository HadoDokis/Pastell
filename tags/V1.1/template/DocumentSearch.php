<?php 

function dateInput($name,$value=''){
	?>
	<input 	type='text' 	
								id='<?php echo $name?>' 
								name='<?php echo $name?>' 
								value='<?php echo $value?>' 
								class='date'
								/>
							<script type="text/javascript">
						   		 jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
								$(function() {
									$("#<?php echo $name?>").datepicker( { dateFormat: 'dd/mm/yy' });
									
								});
							</script>
	<?php 
}

?>
<div class="box_contenu clearfix">

<form class="w700" action='document/search.php' method='get' >
<input type='hidden' name='go' value='go' />
					
<table>
	<tr>
	<th>Type de document</th>
	<td><?php  $documentTypeHTML->displaySelect($type); ?></td>
	</tr>
	<tr>
	<th>Collectivit�</th>
	<td>	
		<select name='id_e'>
			<option value=''>Toutes les collectivit�s</option>
			<?php foreach($arbre as $entiteInfo): ?>
			<option value='<?php echo $entiteInfo['id_e']?>' <?php echo $entiteInfo['id_e'] == $id_e?"selected='selected'":"";?>>
				<?php for($i=0; $i<$entiteInfo['profondeur']; $i++){ echo "&nbsp&nbsp;";}?>
				|_<?php echo $entiteInfo['denomination']?> </option>
			<?php endforeach ; ?>
		</select>
		</td>
	</tr>
	<tr>
		<th>Dernier �tat</th>
		<td><select name='lastetat'>
			<option value=''>N'importe quel �tat</option>
			<?php foreach($listeEtat as $typeDocument => $allEtat): ?>
				<optgroup label="<?php hecho($typeDocument) ?>">
				<?php foreach($allEtat as $nameEtat => $libelle): ?>
				<option value='<?php echo $nameEtat ?>' <?php echo $lastEtat == $nameEtat?"selected='selected'":"";?>>
					<?php echo $libelle ?>
				</option>
					<?php endforeach ; ?>
				</optgroup>
			<?php endforeach ; ?>
			
		</select></td>
	</tr>
	<tr>
		<th>Date de passage dans le dernier �tat</th>
		<td>Du: 	<?php dateInput('last_state_begin',$last_state_begin); ?> <br/>
			Au : <?php dateInput('last_state_end',$last_state_end); ?> 
		</td>
	</tr>
	<tr>
		<th>Pass� par l'�tat</th>
		<td><select name='etatTransit'>
			<option value=''>----</option>
				<?php foreach($listeEtat as $typeDocument => $allEtat): ?>
				<optgroup label="<?php hecho($typeDocument) ?>">
				<?php foreach($allEtat as $nameEtat => $libelle): ?>
				<option value='<?php echo $nameEtat ?>' <?php echo $etatTransit == $nameEtat?"selected='selected'":"";?>>
					<?php echo $libelle ?>
				</option>
					<?php endforeach ; ?>
				</optgroup>
			<?php endforeach ; ?>

		</select></td>
	</tr>
	<tr>
		<th>Date de passage dans cet �tat</th>
		<td>Du: 	<?php dateInput('state_begin',$state_begin); ?> <br/>
			Au : <?php dateInput('state_end',$state_end); ?> 
		</td>
	</tr>
	<tr>
		<th>Dont le titre contient</th>
		<td><input type='text' name='search' value='<?php echo $search?>'/></td>
	</tr>
	<tr>
		<th>Trier le r�sultat</th>
		<td>
			<select name='tri'>
				<?php 
					foreach(array('last_action_date' => "Date de derni�re modification",
									"title" => 'Titre du document',
									"entite" => "Nom de l'entit�",							)
						as $key => $libelle
					) :
				?>
				<option value='<?php echo $key?>' <?php echo $tri==$key?'selected="selected"':''?>><?php echo $libelle?></option>
				<?php endforeach;?>
			</select>
		</td>
	</tr>
</table>
	
	<input type='submit' value='Rechercher' />
</form>
</div>
<?php 

$url = "id_e=$id_e&search=$search&type=$type&lastetat=$lastEtat&last_state_begin=$last_state_begin&last_state_end=$last_state_end&etatTransit=$etatTransit&state_begin=$state_begin&state_end=$state_end&tri=$tri";
if ($go = 'go'){
	
	$listDocument = $documentActionEntite->getListBySearch($id_e,$type,$offset,$limit,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso,$tri);	
	$count = $documentActionEntite->getNbDocumentBySearch($id_e,$type,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso);
	if ($count) {
		$this->SuivantPrecedent($offset,$limit,$count,"document/search.php?$url");
		$documentListAfficheur = new DocumentListAfficheur($documentTypeFactory);
		$documentListAfficheur->affiche($listDocument,$id_e);
		?>
			<a href='document/search-export.php?<?php echo $url?>'>Exporter les informations (CSV)</a>
			<br/><br/>
		<?php 
	} else {
		?>
		<div class="box_info">
			<p>Les crit�res de recherches ne correspondent � aucun document</p>
		</div>
		<?php 
	}
}
