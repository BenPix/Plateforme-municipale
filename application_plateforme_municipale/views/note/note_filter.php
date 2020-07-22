<!-- CSS -->
<style>
* {
  box-sizing: border-box;
}

input[type=text], input[type=number], select, textarea {
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


<button class="btn btn-primary display-4" onclick="showHide()">Afficher les options</button>
<div style="display:none" id="options_de_filtre" class="container container_of_form">
  <?php echo form_open('note/'.$form_action); ?>
  <div class="row">
    <div class="col-25">
      <label>A partir du N° :</label>
    </div>
    <div class="col-75">
    	<?php
    	$id_depart = array(
			'name' => 'id_depart',
			'type' => 'number',
			'step' => '1',
			'min' => '1',
			'value' => $filter_form['id_depart'],
			'placeholder' => 'ex. 159'
		);
		echo form_input($id_depart);
		?>
    </div>
  </div>
  <div class="row">
    <div class="col-25">
      <label>jusqu'au N° :</label>
    </div>
    <div class="col-75">
      <?php
    	$id_fin = array(
			'name' => 'id_fin',
			'type' => 'number',
			'step' => '1',
			'min' => '1',
			'value' => $filter_form['id_fin'],
			'placeholder' => 'ex. 248'
		);
		echo form_input($id_fin);
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
			'value' => $filter_form['date_depart'],
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
			'value' => $filter_form['date_fin'],
			'type' => 'date'
		);
		echo form_input($date_fin);
		?>
    </div>
  </div>
  <div class="row">
    <div class="col-25">
      <label>Rédacteur :</label>
    </div>
    <div class="col-75">
      <?php echo form_dropdown('redacteur_id', html_escape($agents), $filter_form['redacteur_id']); ?>
    </div>
  </div>
  <div class="row">
    <div class="col-25">
      <label>Objet :</label>
    </div>
    <div class="col-75">
    	<?php
    	$objet = array(
			'name' => 'objet',
			'value' => $filter_form['objet'],
			'placeholder' => 'ex. bon de commande'
		);
		echo form_input($objet);
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
  </form>
</div>
<br>
<br>