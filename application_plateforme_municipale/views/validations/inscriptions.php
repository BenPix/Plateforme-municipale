<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">



	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>INSCRIPTIONS A VALIDER</u>
		</p>
		<?php
		$this->table->set_heading('Valider', 'Refuser', 'Nom', 'Prénom', 'Email', 'Pôle');

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(2, 3, 4, 5));

		if ($tableau_inscription->num_rows() !== 0)
			echo $this->table->generate($tableau_inscription);
		?>
	</div>

	<br>

	<div>
		<?php
		if ($tableau_inscription->num_rows() === 0)
			echo '<h4 style="color: red; text-align: center;">PAS D\'INSCRIPTIONS A VALIDER</h4>'
		?>
	</div>

</section>