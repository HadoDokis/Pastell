

<div class="box_contenu">
	Ce message est prot�g� par un mot de passe :
	<form action='mailsec/password-controler.php' method='post'>
		<input type='hidden' name='key' value='<?php hecho($the_key) ?>' />
		<input type='password' name='password' />
		<input type='submit' />
	</form>	

</div>
