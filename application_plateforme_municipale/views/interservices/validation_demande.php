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

    			<script type='text/javascript'> 

                    $(function() {

        $('select[name=delai]').change(function () {

                if ($(this).val() != 'au mieux') {
                    $('#date_souhaitee_select').show();
                    $('#date_souhaitee').prop('required',true);
                } else {
                    $('#date_souhaitee_select').prop('required',false);
                    $('#date_souhaitee_select').hide();
                    $('#date_souhaitee').prop('required',false);
                }

            });

        });


                </script>

    	<?php

		// formulaire pour confirmer/annuler
		echo form_open('validation/confirmer_validation_demande/'.$id, 'class="mbr-form"', array('direction_attachee_id' => $demande->direction_attachee_id));

		echo validation_errors();

		echo '<br><p><strong>Valider cette demande ?</strong></p>';

    	$confirmer = array(
			'name' => 'confirmation',
			'value' => 'Confirmer',
			'style' => 'float:left',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($confirmer);

		$annuler = array(
			'name' => 'confirmation',
			'value' => 'Annuler',
			'style' => 'float:right',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($annuler).'<br><br><br>';

		// affichage de l'information sur la mise à jour à effectuer
		echo '<br><p><strong>Corriger et valider cette demande ?</strong></p>';

		$attr_demande = array(
			'name' => 'demande',
            'value' => set_value('demande', $demande->demande, FALSE),
            'placeholder' => 'Votre demande',
            'required' => '',
            'class' => 'form-control',
            'rows' => 10,
            'cols' => 5
		);
		echo form_label('<u>La demande :</u>').'<br>';
		echo form_textarea($attr_demande).'<br>';

		$attributs = array(
            'required' => '',
            'class' => 'form-control'
		);
		echo form_label('<u>La direction sollicitée :</u>').'<br>';
		echo form_dropdown('direction_sollicitee_id', html_escape($poles), set_value('direction_sollicitee_id', $demande->direction_sollicitee_id, FALSE), $attributs).'<br>';

		$delai = array(
            'au mieux' => 'Au mieux',
            'délai maximum' => 'Délai maximum',
            'date précise' => 'Date précise'
        );
        echo form_label('<u>Le délai :</u>').'<br>';
		echo form_dropdown('delai', $delai, set_value('delai', $demande->delai, FALSE), array('class' => 'form-control')).'<br>';

        if (set_value('delai', $demande->delai, FALSE) == 'au mieux')
            echo ('<div id="date_souhaitee_select" style="display: none;">');
        else
            echo ('<div id="date_souhaitee_select">');

        $attributes2 = array(
                    'style' => 'font-size:14px;'
                );
        echo form_label('<u>Date souhaitée pour la réalisation : </u> (facultatif)');
        echo form_label('<i>( si vous n\'avez pas accès au calendrier, utilisez le format suivant : AAAA-MM-JJ )</i>', '', $attributes2);

        $date = array(
            'name' => 'date_souhaitee',
            'id' => 'date_souhaitee',
            'value' => set_value('date_souhaitee', $demande->date_souhaitee_form, FALSE),
            'type' => 'date',
            'placeholder' => 'jj/mm/aaaa',
            'class' => 'form-control'
        );
        echo form_input($date).'<br></div>';


        // déterminer la provenance par rapport à un sous pole 

        echo '<br><br><p style="font-size: 20px;text-align: center;"><u>PRÉCISER LE SOUS-POLE A L\'ORIGINE DE LA DEMANDE</u></p>';

        echo form_label('<u>La sous-catégorie :</u>').'<br>';
        echo form_dropdown('sous_pole_id', html_escape($sous_poles), '0', array('class' => 'form-control')).'<br>';

		echo form_submit($confirmer);

		echo form_submit($annuler).'<br><br><br>';

		echo form_close();
		?>
			</div>
		</div>
	</div>
</section>