<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<!-- CSS -->
	<style>
	* {
	  box-sizing: border-box;
	}

	input[type=text], select, textarea {
	  width: 100%;
	  padding: 12px;
	  border: 1px solid #ccc;
	  border-radius: 4px;
	  resize: vertical;
	}

	input[type=date] {
	  width: 100%;
	  padding: 12px;
	  border: 1px solid #ccc;
	  border-radius: 4px;
	  resize: vertical;
	}

	label {
	  padding: 12px 12px 12px 0;
	  display: inline-block;
	}

	.container_of_form {
	  border-radius: 5px;
	  background-color: #f2f2f2;
	  padding: 20px;
	  display: block;
	}

	.col-25 {
	  float: left;
	  width: 25%;
	  margin-top: 6px;
	}

	.col-75 {
	  float: left;
	  width: 75%;
	  margin-top: 6px;
	}

	#checkboxes {
		margin: auto;
	}

	/* Clear floats after the columns */
	.row:after {
	  content: "";
	  display: table;
	  clear: both;
	}

	.row {
		width: 98%;
		margin: auto;
	}

	/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
	@media screen and (max-width: 600px) {
	  .col-25, .col-75, input[type=submit] {
	    width: 100%;
	    margin-top: 0;
	  }
	}
	</style>
	<!-- CSS -->

	<!-- JS -->
	<script type='text/javascript'> 

	function showHide() {
	  var x = document.getElementById("options_de_filtre");
	  if (x.style.display === "none") {
	    x.style.display = "block";
	  } else {
	    x.style.display = "none";
	  }
	}

	</script>
	<!-- JS -->


	<button class="btn btn-primary display-4" style="margin-left:65px;" onclick="showHide()">Afficher les options</button>
	<div style="display:none" id="options_de_filtre" class="container container_of_form">
	  <?php echo form_open('historique/demandes_terminees'); ?>
	  <div class="row">
	    <div class="col-25">
	      <label>A partir du dossier N° :</label>
	    </div>
	    <div class="col-75">
	    	<?php
	    	$dossier_depart = array(
				'name' => 'dossier_depart',
				'value' => $this->session->flashdata('filtre_demandes_terminees')['dossier_depart'],
				'placeholder' => 'ex. 159'
			);

			echo form_input($dossier_depart);
			?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>jusqu'au dossier N° :</label>
	    </div>
	    <div class="col-75">
	      <?php
	    	$dossier_fin = array(
				'name' => 'dossier_fin',
				'value' => $this->session->flashdata('filtre_demandes_terminees')['dossier_fin'],
				'placeholder' => 'ex. 248'
			);
			echo form_input($dossier_fin);
			?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>Horodateur : du</label>
	    </div>
	    <div class="col-75">
	      <?php
	      $date_depart = array(
				'name' => 'date_depart',
				'value' => $this->session->flashdata('filtre_demandes_terminees')['date_depart'],
				'type' => 'date'
			);
			echo form_input($date_depart);
			?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>au</label>
	    </div>
	    <div class="col-75">
	      <?php
	      $date_fin = array(
				'name' => 'date_fin',
				'value' => $this->session->flashdata('filtre_demandes_terminees')['date_fin'],
				'type' => 'date'
			);
			echo form_input($date_fin);
			?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>Direction attachée :</label>
	    </div>
	    <div class="col-75">
	      <?php echo form_dropdown('expediteur', html_escape($pole_attache), $this->session->flashdata('filtre_demandes_terminees')['expediteur']); ?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>Direction sollicitée :</label>
	    </div>
	    <div class="col-75">
	      <?php echo form_dropdown('destinataire', html_escape($pole_sollicite), $this->session->flashdata('filtre_demandes_terminees')['destinataire']); ?>
	    </div>
	  </div>
	  <div class="row">
	    <?php
	    $filtrer = array(
			'name' => 'filtrer',
			'value' => 'Filtrer',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);

		echo form_submit($filtrer);
		?>
	  </div>
	  <br>
	  <hr>
	  </form>
	</div>

	<br>
	<br>
	<br>

	<div>


		<!-- tableau des demandes terminées -->


		<p style="font-size: 20px;text-align: center;">
			<u>LES DEMANDES TERMINÉES</u>
		</p>
		<?php
		echo $pagination.'<br>';

		$this->table->set_heading('N° Dossier', 'Horodateur', 'Demandeur', 'Direction attachée', 'Direction sollicitée', 'Demande');

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab_reception">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(2, 3, 4, 5));

		if ($tab->num_rows() > 0)
			echo $this->table->generate($tab);
		?>
	</div>

	<br>

	<div>
		<?php
		if ($tab->num_rows() === 0)
			echo '<h4 style="color: red; text-align: center;">AUCUNE DEMANDE TERMINÉE</h4>'
		?>
	</div>


</div>
</section>