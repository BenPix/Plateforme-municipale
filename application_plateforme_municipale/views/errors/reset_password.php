<div class="row py-2 justify-content-center">
	<div class="col-12 col-lg-6  col-md-8 ">
		<p style="color: red">Vous disposiez de 10 minutes pour réinitialiser votre mot de passe.</p>
		<p style="color: red">Soit ces 10 minutes sont passées, auquel cas il vous faudra recommencer l'opération.<br>Soit votre mot de passe à déjà été réinitialisé.</p>
		<?php
		echo anchor(base_url(), 'Lien vers le site', 'class="btn btn-primary  display-4"');
		echo anchor(base_url().'index.php/login/mot_de_passe_oublie', 'Réessayer', 'class="btn btn-primary  display-4"');
		?>
	</div>
</div>