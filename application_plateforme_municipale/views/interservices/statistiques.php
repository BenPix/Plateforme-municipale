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
  <?php echo form_open('statistique/gestion_interservices'); ?>
  <div class="row">
    <div class="col-25">
      <label>A partir du dossier N° :</label>
    </div>
    <div class="col-75">
    	<?php
    	$dossier_depart = array(
			'name' => 'dossier_depart',
			'value' => $this->session->flashdata('values')['dossier_depart'],
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
			'value' => $this->session->flashdata('values')['dossier_fin'],
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
			'value' => $this->session->flashdata('values')['date_depart'],
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
			'value' => $this->session->flashdata('values')['date_fin'],
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
      <?php echo form_dropdown('expediteur_id', html_escape($pole_attache), $this->session->flashdata('values')['expediteur_id']); ?>
    </div>
  </div>
  <div class="row">
    <div class="col-25">
    	<label>Direction sollicitée :</label>
    </div>
    <div class="col-75">
      <?php echo form_dropdown('destinataire_id', html_escape($pole_sollicite), $this->session->flashdata('values')['destinataire_id']); ?>
    </div>
    
  </div>
  <div class="row">
    <div class="col-25">
      <label>Statut</label>
    </div>
    <div class="col-75" id="checkboxes">
      <?php
      $checkbox1 = array(
            'name'          => 'statut[]',
            'value'         => 'en attente',
            'checked'       => (NULL !== $this->session->flashdata('values')['en_attente']) ? $this->session->flashdata('values')['en_attente'] : TRUE,
        );
        $checkbox2 = array(
            'name'          => 'statut[]',
            'value'         => 'en cours',
            'checked'       => (NULL !== $this->session->flashdata('values')['en_cours']) ? $this->session->flashdata('values')['en_cours'] : TRUE,
            'style'         => 'margin-left:40px'
        );
        $checkbox3 = array(
            'name'          => 'statut[]',
            'value'         => 'terminé',
            'checked'       => (NULL !== $this->session->flashdata('values')['termine']) ? $this->session->flashdata('values')['termine'] : TRUE,
            'style'         => 'margin-left:40px'
        );
		echo form_checkbox($checkbox1).'en attente';
		echo form_checkbox($checkbox2).'en cours';
		echo form_checkbox($checkbox3).'terminé';
		?>
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

		<?php

		echo form_label('<u>Exporter le tableau sous format Excel :</u>').'<br>';
		echo anchor('statistique/export', 'Exporter', 'class="btn btn-primary display-4" target="_blank"');
		/*
		* 
		*
		*
		*
		* formulaire de création du graphique
		*
		*
		*
		*
		*
		*/
		echo form_open('statistique/graphique', 'target="_blank"');
		echo form_label('<u>Graphique par rapport à la colonne :</u>').'<br>';
		$options = array(
			'direction_attachee' => 'Direction attachée',
			'direction_sollicitee' => 'Direction sollicitée',
			'statut' => 'Statut'
		);
		echo form_dropdown('part', $options, NULL, array('style' => 'width:30%')).'<br><br>';
		$graphique = array(
			'name' => 'graphique',
			'value' => 'Afficher le graphique',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($graphique);
		echo form_close();
		?>
</div>

	<br>

	<div>
		<p style="font-size: 20px;text-align: center;">Il y a un total de <?php echo $total ?> demande(s) après filtrage.</p>
	</div>

	<div>
		<p style="font-size: 20px;text-align: center;">
			<u>DEMANDES</u>
		</p>
		<?php
    echo $pagination.'<br>';

		$this->table->set_heading('N° Dossier', 'Horodateur', 'Direction attachée', 'Direction sollicitée', 'Demande', 'Date souhaitée', 'Statut');

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

    $this->table->function = 'htmlspecialchars';

    $this->table->set_columns_for_callback_function(array(2, 3, 4));

		echo $this->table->generate($tableau);
		?>
	</div>


</section>