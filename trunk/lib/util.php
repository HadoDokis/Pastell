<?php

function hecho($message,$quot_style=ENT_QUOTES){
	echo htmlentities($message,$quot_style);
}

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

function getDateIso($value){
	if ( ! $value){
		return "";
	}
	return preg_replace("#^(\d{2})/(\d{2})/(\d{4})$#",'$3-$2-$1',$value);
}