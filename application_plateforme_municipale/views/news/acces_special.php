<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>AGENTS PROFITANT D'UN ACCÈS SPÉCIAL</u>
		</p>
		<?php
		$this->table->set_heading($headings);

		
		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		echo $this->table->generate($tableau);
		?>
	</div>
	<br><br>


	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>ACCÈS EXCEPTIONNEL AUX CATÉGORIES</u>
		</p>
	</div>
	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">

			<?php
				echo validation_errors().'<br>';

				echo form_open('accueil/attribuer_acces_special', 'class="mbr-form"');

				echo form_label('<u>Attribuer un accès spécial à l\'agent :</u>');
				echo form_dropdown('agent', $agents, set_value('agent'), array('class' => 'form-control')).'<br>';

				echo form_label('<u>Les catégories accessibles :</u>');
				foreach ($categories as $row) {

                    $data = array(
                        'name' => 'categorie[]',
                        'value' => $row->id,
                        'checked' => FALSE
                    );
                    echo '<label class="container-of-checkbox" title="Destiné aux '.htmlspecialchars($row->cible).'"><strong>'.htmlspecialchars($row->nom).'</strong>';
                    echo form_checkbox($data);
                    echo '<span class="mark-of-checkbox"></span>';
                    echo '</label>';
                }

				$valider = array(
					'name' => 'valider',
					'value' => 'Valider',
					'type' => 'submit',
					'class' => 'btn btn-primary  display-4'
				);
				echo form_submit($valider);

				echo form_close();
			?>
		</div>
	</div>
</section>