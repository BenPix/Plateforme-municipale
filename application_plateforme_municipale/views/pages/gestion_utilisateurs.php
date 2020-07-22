<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">
			<p><strong style="font-size: 18px;"><u><?php echo $search_title ?></u></strong>&nbsp;<?php echo $anchor ?></p><br>
		<?php
			echo validation_errors();

			echo form_open($form_action);

			echo form_label('<u>Nom de l\'agent : </u>').'<br>';

			$nom = array(
				'name' => 'nom',
				'value' => set_value('nom', '', FALSE),
				'placeholder' => 'ou une partie du nom',
				'required' => '',
				'class' => 'form-control'
			);
			echo form_input($nom).'<br>';

			$rechercher = array(
				'name' => 'action',
				'value' => 'Rechercher',
				'type' => 'submit',
				'class' => 'btn btn-primary  display-4'
			);
			echo form_submit($rechercher);

			echo form_close();
		?>
		</div>
	</div>
	<br><br>
	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>UTILISATEURS</u>
		</p>
		<?php

		echo $pagination;
		echo '<br>';

		$this->table->set_heading($heading);

		
		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(2, 3, 4, 5, 6));

		if ($tableau->num_rows() !== 0)
			echo $this->table->generate($tableau);

		echo $pagination;
		?>
	</div>
	<br>
	<div>
		<?php
		if ($tableau->num_rows() === 0)
			echo '<h4 style="color: red; text-align: center;">AUCUN UTILISATEUR TROUVÉ</h4>';
		?>
	</div>

</div>
</section>