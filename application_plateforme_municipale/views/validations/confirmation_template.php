<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div>
		<p style="font-size: 20px;text-align: center;"><u><?php echo $titre_tableau ?></u></p>
		<?php

		// affichage du tableau
		$this->table->set_heading($heading);

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function($to_escape);

		echo $this->table->generate($tab);

		?>
		<br>
		<div class="row py-2 justify-content-center">
			<div class="col-12 col-lg-6  col-md-8 ">
				<?php

				// affichage de l'information sur la mise à jour à effectuer
				echo form_label($phrase);

				// formulaire pour confirmer/annuler
				echo form_open($form_action);

				$confirmer = array(
					'name' => 'confirmation',
					'value' => 'Confirmer',
					'class' => 'btn btn-primary  display-4'
				);

				echo form_submit($confirmer);

				$annuler = array(
					'name' => 'confirmation',
					'value' => 'Annuler',
					'class' => 'btn btn-primary  display-4',
					'style' => 'float: right;'
				);

				echo form_submit($annuler);

				echo form_close();
				?>
			</div>
		</div>
	</div>
</section>