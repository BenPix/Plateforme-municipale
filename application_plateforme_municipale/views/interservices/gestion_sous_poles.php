<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">
			<div style="border: solid black 2px; background-color: #A9A9A9; font-size: 30px; text-align: center;">DESCRIPTION DES SOUS-PÔLES</div>
			<br><br><br><br>
		</div>
	</div>
	
	<div style="width: 60%;margin: 0 auto;">
		<?php
		echo validation_errors();

		$this->table->set_heading($heading);
		$tmpl = array(
			'table_open' => '<table border="solid" cellpadding="4" cellspacing="0" class="tab-pole">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(1, 2));

		// fonction permettant de rajouter une exception
		// pour la colonne x, on escape ce qui se trouve entre les y et z char spécifiés du string array(x, y, z)
		$this->table->exception = array(1, 31, 9); // colonne 1, oter 31 char en debut et 9 char en fin et escape le reste

		echo $this->table->generate($tab);
		?>
	</div>

</section>
<div class="row py-2 justify-content-center">
	<div class="col-12 col-lg-6  col-md-8 ">

		<?php
		echo form_open('gestion/sous_pole_create');
		echo form_fieldset('<u>Nouveau Sous-Pôle</u>');

		echo form_label('<u>Désignation : </u>');
		$designation = array(
			'name' => 'designation',
			'placeholder' => 'ex. Service Communication, École Saint-Exupéry, ...',
			'value' => set_value('designation'),
			'required' => '',
			'class' => 'form-control'
		);
		echo form_input($designation).'<br>';

		echo form_label('<u>Pôle Mère : </u>');
		echo form_dropdown('pole_mere', html_escape($pole), '', array('class' => 'form-control')).'<br>';

		echo form_label('<u>Choisir la couleur en rapport avec ce service pour la vision dans l\'historique :</u>').'<br>';
		$couleur = array(
			'name' => 'couleur',
			'value' => set_value('couleur'),
			'type' => 'color',
			'style' => 'width:50px;height:50px;'
		);
		echo form_input($couleur).'<br><br>';

		$ajouter = array(
			'name' => 'ajouter',
			'value' => 'Ajouter',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($ajouter).'<br><br>';

		echo form_close();
		?>
	</div>
</div>