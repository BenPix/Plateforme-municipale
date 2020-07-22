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
	  <?php echo form_open('historique/demandes_envoyees'); ?>
	  <div class="row">
	    <div class="col-25">
	      <label>A partir du dossier N° :</label>
	    </div>
	    <div class="col-75">
	    	<?php
	    	$dossier_depart = array(
				'name' => 'dossier_depart',
				'value' => $this->session->flashdata('filtre_demandes_envoyees')['dossier_depart'],
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
				'value' => $this->session->flashdata('filtre_demandes_envoyees')['dossier_fin'],
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
				'value' => $this->session->flashdata('filtre_demandes_envoyees')['date_depart'],
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
				'value' => $this->session->flashdata('filtre_demandes_envoyees')['date_fin'],
				'type' => 'date'
			);
			echo form_input($date_fin);
			?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>Direction sollicitée :</label>
	    </div>
	    <div class="col-75">
	      <?php echo form_dropdown('destinataire', html_escape($pole_sollicite), $this->session->flashdata('filtre_demandes_envoyees')['destinataire']); ?>
	    </div>
	  </div>
	  <div class="row">
	    <div class="col-25">
	      <label>Service originaire :</label>
	    </div>
	    <div class="col-75">
	      <?php echo form_dropdown('sous_pole', html_escape($sous_pole), $this->session->flashdata('filtre_demandes_envoyees')['sous_pole']); ?>
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
	            'checked'       => (NULL !== $this->session->flashdata('demandes_envoyees_en_attente')) ? $this->session->flashdata('demandes_envoyees_en_attente') : TRUE,
	        );
	        $checkbox2 = array(
	            'name'          => 'statut[]',
	            'value'         => 'en cours',
	            'checked'       => (NULL !== $this->session->flashdata('demandes_envoyees_en_cours')) ? $this->session->flashdata('demandes_envoyees_en_cours') : TRUE,
	            'style'         => 'margin-left:40px'
	        );
			echo form_checkbox($checkbox1).'en attente';
			echo form_checkbox($checkbox2).'en cours';
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
	</div>

	<br>
	<br>
	<br>

	<div>

	<!-- légende affichant les couleurs et les sous-pole associés -->

		<?php
		if (!empty($legende_couleurs)) {
			echo '<div style="margin-left: 60px;">';
				echo '<h5><u>Légende des Services associés aux Pôles</u></h5>';
				
				foreach ($legende_couleurs as $row) {
					$couleur = empty($row->couleur) ? '#000000' : $row->couleur;
					$nom = empty($row->nom) ? 'Aucun' : htmlspecialchars($row->nom);
					echo '<div style="overflow:hidden;"><div style="width:20px;height:20px;border-radius:10px;background:'.$couleur.';float:left;"></div><span style="color:'.$couleur.';margin-left:5px;overflow;hidden">'.$nom.'</span></div>';
				}
				
			echo '</div>';
		}
		?>

		<!-- tableau des demandes envoyées -->


		<p style="font-size: 20px;text-align: center;">
			<u>VOS DEMANDES</u>
		</p>
		<?php
		echo $pagination.'<br>';

		$this->table->set_heading('N° Dossier', 'Horodateur', 'Direction sollicitée', 'Demande', 'Délai', 'Degré d\'urgence', 'Statut');

		$tmpl = array(
			'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab_reception">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		foreach ($tab_envoi_avec_couleur->result() as $row) {
			if (!empty($row->couleur)) {
				$couleur = str_replace('#', '', $row->couleur);
				$r = hexdec($couleur[0].$couleur[1]);
				$g = hexdec($couleur[2].$couleur[3]);
				$b = hexdec($couleur[4].$couleur[5]);
				$couleur = 'rgba('.$r.','.$g.','.$b.',0.1)';
			} else {
				$couleur = '';
			}
			// background de couleur
			$couleur = 'background-color:'.$couleur.';';
			$style = $couleur;
			$num_dossier = array(
				'style' => $style,
				'data' => $row->num_dossier
			);
			$horodateur = array(
				'style' => $style,
				'data' => $row->horodateur
			);
			$pole_attache = array(
				'style' => $style,
				'data' => $row->pole_sollicite
			);
			$demande = array(
				'style' => $style,
				'data' => $row->demande
			);
			$delai = array(
				'style' => $style,
				'data' => $row->delai
			);
			$degre_urgence = array(
				'style' => $style,
				'data' => $row->degre_urgence
			);
			$etat = array(
				'style' => $style,
				'data' => $row->etat
			);
			$this->table->add_row($num_dossier, $horodateur, $pole_attache, $demande, $delai, $degre_urgence, $etat);
		}

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function(array(2, 3));

		if ($tab_envoi_avec_couleur->num_rows() > 0)
			echo $this->table->generate();
		?>
	</div>

	<br>

	<div>
		<?php
		if ($tab_envoi_avec_couleur->num_rows() === 0)
			echo '<h4 style="color: red; text-align: center;">AUCUNE DEMANDE EN COURS</h4>'
		?>
	</div>


</div>
</section>