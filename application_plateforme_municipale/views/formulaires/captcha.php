<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>ENREGISTREMENT D'UNE CLÉ RECAPTCHA V2 DE GOOGLE</u>
		</p>
	</div>
	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">
			<p>Le reCAPTCHA v2 de Google permet de protéger l'utilisation de robot sur la page d'inscription. Il est plus efficace que le captcha par défaut de l'application, mais nécessite de posséder un compte Google et de configurer son reCAPTCHA.</p>
			<br><hr><br>

			<?php

			echo form_open('gestion/captcha', 'class="mbr-form"');

			echo heading('<u>Enregistrement des clés</u>', 5).'<br>';

			echo validation_errors();
			echo isset($enregistrement) ? $enregistrement : '';

			echo form_label('Clé du site de votre reCAPTCHA Google :');
			$sitekey = array(
				'name' => 'sitekey',
				'value' => set_value('sitekey', $data_sitekey),
				'class' => 'form-control'
			);
			echo form_input($sitekey).'<br>';

			echo form_label('Clé secrète de votre reCAPTCHA Google :');
			$secretkey = array(
				'name' => 'secretkey',
				'value' => set_value('secretkey', $data_secretkey),
				'class' => 'form-control'
			);
			echo form_input($secretkey).'<br>';

			if ( ! empty($data_sitekey) ) echo form_label('Si les 2 champs ci-dessus sont vides, cliquer sur Enregistrer va supprimer les données de vos clés, et le captcha par défaut sera désormais utilisé.');
			$enregistrer = array(
				'name' => 'enregistrer',
				'value' => 'Enregistrer',
				'type' => 'submit',
				'class' => 'btn btn-primary  display-4'
			);
			echo form_submit($enregistrer);

			echo form_close();
			?>
			<br><hr><br>
			<h5>Comment créer des clés Google reCAPTCHA V2 ?</h5> 
			<ol>
				<li>Visitez <a href="https://www.google.com/recaptcha">www.google.com/recaptcha</a></li>
				<li>Cliquez sur le bouton <u>Admin console</u> en haut à droite</li>
				<li>Connectez vous avec votre compte gmail (si ce n'est pas déjà fait)</li>
				<li>Sous "Libellé" ou "Label", saisissez le domaine du site : <u><?php echo $domain ?></u></li>
				<li>Sous "Type de reCAPTCHA" ou "reCAPTCHA type", cochez <u>reCAPTCHA version 2</u> ou <u>reCAPTCHA v2</u></li>
				<li>Cochez ensuite <u>Case à cocher "Je ne suis pas un robot"</u> ou <u>"I'm not a robot" tickbox</u></li>
				<li>Sous "Domaines" ou "Domains", saisissez le domaine du site : <u><?php echo $domain ?></u> pour y autoriser son utilisation</li></li>
				<li>Cochez la case <u>Accepter les conditions d'utilisation de reCAPTCHA</u> ou <u>Accept the reCAPTCHA Terms of Service</u></li>
				<li>Finalisez la création en cliquant sur  <u>ENVOYER</u> ou <u>SUBMIT</u></li>
				<li>La page suivante vous donne les valeurs des 2 clés à saisir dans ce formulaire</li>
			</ol>
			<ul>
				<li>Il est possible de retrouver ces 2 valeurs en suivant les étapes 1, 2 et 3, puis en sélectionnant le site, en haut à gauche, à côté de "v2 Case à cocher", puis en cliquant sur la roue dentée. Sur la page suivante, il suffit de cliquer sur <u>Clés reCAPTCHA</u>.</li>
			</ul>
		</div>
	</div>

</section>