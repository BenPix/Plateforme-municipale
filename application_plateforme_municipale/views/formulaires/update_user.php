<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div>
		<div class="row py-2 justify-content-center">
			<div class="col-12 col-lg-6  col-md-8 ">

				<?php
                echo validation_errors();

				echo form_open($form_action);

                $attributes1 = array('style' => 'font-size:1.5em; font-weight: bold;');
                $attributes2 = array('style' => 'font-size:0.8em; font-style: italic;');
                $attributes3 = array('style' => 'font-size:1em; text-decoration: underline;');

				echo form_label('Données de l\'utilisateur', '', $attributes1).'<br>';
				echo form_label($instructions).'<br><br>';

				$attribut = array('class' => 'form-control', 'id' => 'choix_pole');
				echo form_label('Appartenance :', '', $attributes3);
				echo form_dropdown('pole', html_escape($pole), html_escape($utilisateur->pole_id), $attribut).'<br>';

				$nom = array(
					'name' => 'nom',
					'placeholder' => 'Nom',
					'value' => set_value('nom', $utilisateur->nom, FALSE),
					'required' => '',
					'class' => 'form-control'
				);
				echo form_label('Nom :', '', $attributes3);
				echo form_input($nom).'<br>';

				$prenom = array(
					'name' => 'prenom',
					'placeholder' => 'Prénom',
					'value' => set_value('prenom', $utilisateur->prenom, FALSE),
					'required' => '',
					'class' => 'form-control'
				);
				echo form_label('Prénom :', '', $attributes3);
				echo form_input($prenom).'<br>';

				$email = array(
					'name' => 'email',
					'type' => 'email',
					'placeholder' => 'Email',
					'value' => set_value('email', $utilisateur->email, FALSE),
					'required' => '',
					'class' => 'form-control',
					'id' => 'email-form3-2'
				);
				echo form_label('Adresse mail :', '', $attributes3);
				echo form_input($email).'<br>';

				echo form_label('Rang à attribuer', '', $attributes1).'<br>';
				echo form_dropdown('rang', $rangs, set_value('rang', $utilisateur->rang, FALSE), array('class' => 'form-control')).'<br>';

				?>
				<div style="font-size: 14px; font-style: italic;">
                    <?php echo $notice_attribution_rang; ?>
				</div>
				<?php

				$valider = array(
					'name' => 'confirmation',
					'value' => $button,
					'class' => 'btn btn-primary  display-4',
				);
				echo form_submit($valider).'<br>';

				$annuler = array(
					'name' => 'confirmation',
					'value' => 'Annuler',
					'class' => 'btn btn-primary  display-4',
				);
				echo form_submit($annuler);

				echo form_close();
				?>
			</div>
		</div>
	</div>
</section>