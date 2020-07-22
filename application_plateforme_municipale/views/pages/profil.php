<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<?php echo validation_errors(); ?>
	<?php echo $error; ?>

	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>VOTRE PROFIL ACTUEL</u>
		</p>
		<?php
		$this->table->set_heading('Nom', 'Prénom', 'Email', 'Pôle');

		
		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		echo $this->table->generate($tableau);
		?>
	</div>
	<br><br>
	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>MODIFICATIONS</u>
		</p>
	</div>
	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">
			<!-- formulaire de login -->

			<?php

			echo form_open('accueil/modifier_profil', 'class="mbr-form"', array('pseudo' => $this->session->userdata('pseudo')));

			echo form_label('Veillez à ne modifier que les champs nécessaires.');

			$nom = array(
				'name' => 'nom',
				'placeholder' => 'Nom',
				'value' => $this->session->userdata('nom'),
				'required' => '',
				'class' => 'form-control'
			);

			echo form_input($nom).'<br>';

			$prenom = array(
				'name' => 'prenom',
				'placeholder' => 'Prénom',
				'value' => $this->session->userdata('prenom'),
				'required' => '',
				'class' => 'form-control'
			);

			echo form_input($prenom).'<br>';

			$email = array(
				'name' => 'email',
				'value' => $this->session->userdata('email'),
				'type' => 'email',
				'placeholder' => 'Email',
				'required' => '',
				'class' => 'form-control',
				'id' => 'email-form3-2'
			);

			echo form_input($email).'<br>';

			$password = array(
				'name' => 'password',
				'placeholder' => 'Mot de passe (facultatif)',
				'class' => 'form-control'
			);

			echo form_password($password).'<br>';

			$password_confirm = array(
				'name' => 'password_confirm',
				'placeholder' => 'Confirmer le Mot de passe (facultatif)',
				'class' => 'form-control'
			);

			echo form_password($password_confirm).'<br>';

			echo form_label('Pour modifier d\'autres caractéristiques de votre profil, veuillez contacter la direction.');

			$modifier = array(
				'name' => 'modifier',
				'value' => 'Modifier',
				'type' => 'submit',
				'class' => 'btn btn-primary  display-4'
			);

			echo form_submit($modifier);

			echo form_close();
			?>
		</div>
	</div>




	<br><br>

	<div<?php if ( ! $is_ancien_responsable && empty($delegue_potentiel) ) echo ' style="display:none;"'; ?>>
		<p style="font-size: 20px;text-align: center;">
			<u>DÉLÉGUER SON RÔLE DE RESPONSABLE</u>
		</p>
	</div>
	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">

			<?php
				if ( ! empty($delegue_potentiel) ) {
					echo form_open('accueil/deleguer', 'class="mbr-form"');

					echo form_label('à l\'agent :');
					echo form_dropdown('delegue_id', $delegue_potentiel, '', array('class' => 'form-control')).'<br>';

					$deleguer = array(
						'name' => 'delegue',
						'value' => 'Déléguer',
						'type' => 'submit',
						'class' => 'btn btn-primary  display-4'
					);
					echo form_submit($deleguer);

					echo form_close();
				} elseif ($is_ancien_responsable) {
					echo form_open('accueil/desactiver_delegation', 'class="mbr-form"');

					echo form_label('Votre rôle de responsable a été délégué à '.$delegue);
					$desactiver_delegation = array(
						'name' => 'desactiver_delegation',
						'value' => 'Désactiver la délégation',
						'type' => 'submit',
						'class' => 'btn btn-primary  display-4'
					);
					echo form_submit($desactiver_delegation);

					echo form_close();
				}
			?>
		</div>
	</div>

</section>