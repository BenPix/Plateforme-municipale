<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<?php if (NULL !== $this->session->flashdata('error')) {
		echo $this->session->flashdata('error');
	} ?>

	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>DEMANDES INTERSERVICES A VALIDER</u>
		</p>
		<?php
		$this->table->set_heading('Valider', 'Refuser', 'Horodateur', 'Demandeur', 'Direction sollicitée', 'Demande', 'Délai', 'Degré d\'urgence', 'Pièces jointes');

		

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(3, 4, 5));

		if ($tableau_demande->num_rows() != 0)
			echo $this->table->generate($tableau_demande);
		?>
	</div>
	<br>
	<div>
		<?php
		if ($tableau_demande->num_rows() == 0)
			echo '<h4 style="color: red; text-align: center;">PAS DE DEMANDE A VALIDER</h4>';
		?>
	</div>
	
</section>