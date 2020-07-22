<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div>
		<p style="font-size: 20px;text-align: center;"><u>LA DEMANDE</u></p>
		<?php

		// affichage du tableau
		$this->table->set_heading('Horodateur', 'Demandeur', 'Direction sollicitée', 'Demande', 'Délai', 'Degré d\'urgence');

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(1, 2, 3));

		echo $this->table->generate($tab_demande);

		?>
		<div class="row py-2 justify-content-center">
    		<div class="col-12 col-lg-6  col-md-8 ">
    			<?php


				// affichage de l'information sur la mise à jour à effectuer
				echo '<br><p>Refuser cette demande ?</p>';

				echo validation_errors();

				// formulaire pour confirmer/annuler
				echo form_open('validation/confirmer_refus_demande/'.$id);

				echo form_label('<u>Raison du refus : </u>').'<br>';
				$textarea = array(
					'name' => 'refus',
					'placeholder' => 'Champs obligatoire',
					'value' => set_value('refus'),
					'class' => 'form-control',
					'rows' => '3',
					'cols' => '5'
				);
				echo form_textarea($textarea).'<br>';

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