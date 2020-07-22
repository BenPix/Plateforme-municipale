
<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

    <div>

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

        <p style="font-size: 20px;text-align: center;"><u>DÉTAILS DE LA DEMANDE</u></p>
        <?php

        // affichage du tableau
        $this->table->set_heading('N° Dossier', 'Horodateur', 'Demandeur', 'Direction attachée', 'Direction sollicitée', 'Délai', 'Degré d\'urgence', 'Date relance', 'Statut');

        $tmpl = array(
            'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab">',
            'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
        );

        $this->table->set_template($tmpl);

        $this->table->function = 'htmlspecialchars';

        $this->table->set_columns_for_callback_function(array(2, 3, 4));
        
        echo $this->table->generate($tab_demande).'<br>';

        ?>

        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">


        <?php 
        echo validation_errors();
        if (isset($error)) {
            echo $error;
        }
        if (!empty($demande->pieces_jointes)) echo form_label('<u>Télécharger les pièces jointes : </u><br>'.$demande->pieces_jointes).'<br>';

        $hidden = array(
            'direction_sollicitee_id' => $demande->direction_sollicitee_id
        );
        echo form_open($form_open, 'class="mbr-form"', $hidden);

        echo form_label('<u>La demande : </u>');
        $textarea = array(
            'name' => 'demande',
            'value' => set_value('demande', $demande->demande, FALSE),
            'disabled' => '',
            'required' => '',
            'class' => 'form-control',
            'rows' => '10',
            'cols' => '40'
        );
        echo form_textarea($textarea).'<br>';
        /*
        *
        *
        *
        *
        *
        * COMMENTAIRES
        *
        *
        *
        *
        *
        */
        if ( ! empty($commentaires) ) echo '<br><br><p style="font-size: 20px;text-align: center;"><u>COMMENTAIRES</u></p>';

        // test de boucle <input> avec les commentaires
        foreach ($commentaires as $row) {
            echo 'Posté par <strong>'.$row->agent.'</strong> le '.$row->horodateur.'<br>';
            $comms = array(
                'value' => $row->commentaire,
                'disabled' => '',
                'class' => 'form-control',
                'rows' => $row->nombreLignes
            );
            echo form_textarea($comms).'<br>';
        }

        if ( ! empty($commentaire_possible) ) {
            echo form_label('<u>Laisser un commentaire : </u>');
            $commentaire = array(
                'name' => 'commentaire',
                'value' => set_value('commentaire', '', FALSE),
                'lock' => '',
                'class' => 'form-control',
                'rows' => '4',
                'cols' => '40'
            );
            echo form_textarea($commentaire).'<br>';

            $commenter = array(
                'name' => 'maj',
                'value' => 'Poster le commentaire',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($commenter).'<br><br>';
        }
        /*
        *
        *
        *
        *
        *
        * STATUT
        *
        *
        *
        *
        *
        */
        if ($direction_sollicitee == 'direction_sollicitee') {
            echo '<br><hr><br><br><p style="font-size: 20px;text-align: center;"><u>METTRE A JOUR LE STATUT</u></p>';

            echo form_label('<u>Statut de la demande :</u>').'<br>';
            $options = array(
                '1' => 'en attente',
                '2' => 'en cours',
                '3' => 'terminé',
                '4' => 'refusé'
            );
            echo form_dropdown('statut', $options, $demande->etat, array('class' => 'form-control')).'<br>';
            echo form_label('<u>Si refusé, raison du refus : </u>').'<br>';
            $textarea = array(
                'name' => 'refus',
                'placeholder' => 'Champs obligatoire si c\'est un refus',
                'class' => 'form-control',
                'style' => 'max-width:800px;',
                'rows' => '3',
                'cols' => '5'
            );
            echo form_textarea($textarea).'<br>';
            $valider = array(
                'name' => 'maj',
                'value' => 'Mettre à jour',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4',
            );
            echo form_submit($valider).'<br><br>';
        }
        /*
        *
        *
        *
        *
        *
        * RELANCE
        *
        *
        *
        *
        *
        */
        if ($direction_attachee == 'direction_attachee') {
            echo '<br><hr><br><br><p style="font-size: 20px;text-align: center;"><u>RELANCER LA DEMANDE</u></p>';

            echo form_label('<i style="font-size:12px">(Vous pouvez modifier le degré d\'urgence)</i>').'<br>';
            $checkbox1 = array(
                'name'          => 'urgence',
                'value'         => '1',
                'checked'       => ($demande->degre_urgence == 1) ? TRUE : FALSE,
                'style'         => 'margin-left:10px'
            );
            $checkbox2 = array(
                'name'          => 'urgence',
                'value'         => '2',
                'checked'       => ($demande->degre_urgence == 2) ? TRUE : FALSE,
                'style'         => 'margin-left:40px'
            );
            $checkbox3 = array(
                'name'          => 'urgence',
                'value'         => '3',
                'checked'       => ($demande->degre_urgence == 3) ? TRUE : FALSE,
                'style'         => 'margin-left:40px'
            );
            $checkbox4 = array(
                'name'          => 'urgence',
                'value'         => '4',
                'checked'       => ($demande->degre_urgence == 4) ? TRUE : FALSE,
                'style'         => 'margin-left:40px'
            );
            $checkbox5 = array(
                'name'          => 'urgence',
                'value'         => '5',
                'checked'       => ($demande->degre_urgence == 5) ? TRUE : FALSE,
                'style'         => 'margin-left:40px'
            );
            $label = array(
            'style'  => 'margin-left:10px;'
        );
            echo form_label('Pas d\'urgence');
            echo form_radio($checkbox1).'1';
            echo form_radio($checkbox2).'2';
            echo form_radio($checkbox3).'3';
            echo form_radio($checkbox4).'4';
            echo form_radio($checkbox5).'5';
            echo form_label('Très urgent', '', $label).'<br><br>';
            $relancer = array(
                'name' => 'maj',
                'value' => 'Relancer',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($relancer).'<br><br>';
        }
        /*
        *
        *
        *
        *
        *
        * AFFECTER A UN SOUS POLE
        *
        *
        *
        *
        *
        */
        if ( ! empty($affectation_possible) ) {
            // possibilité de préciser le destinataire (sous pole)
            echo '<br><hr><br><br><p style="font-size: 20px;text-align: center;"><u>AFFECTER LA DEMANDE A UN SERVICE SPÉCIFIQUE</u></p>';

            echo form_label('<u>La sous-catégorie :</u>').'<br>';
            echo form_dropdown('sous_pole_id', html_escape($sous_poles), html_escape($demande->sous_pole_id), array('class' => 'form-control')).'<br>';

            $affecter = array(
                'name' => 'maj',
                'value' => 'Affecter',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($affecter);
        }
        /*
        *
        *
        *
        *
        *
        * MODIFIER LE DELAIS
        *
        *
        *
        *
        *
        */
        if ( ! empty($modif_delai_possible) ) {
            // possibilité de préciser le destinataire (sous pole)
            echo '<br><hr><br><br><p style="font-size: 20px;text-align: center;"><u>MODIFIER LE DÉLAI DE LA DEMANDE</u></p>';

            $delai = array(
                'au mieux' => 'Au mieux',
                'délai maximum' => 'Délai maximum',
                'date précise' => 'Date précise'
            );
            echo form_label('<u>Délai : </u>').'<br>';
            echo form_dropdown('delai', $delai, set_value('delai', $demande->delai, FALSE), array('class' => 'form-control')).'<br>';

            if (set_value('delai', $demande->delai, FALSE) === 'au mieux' || set_value('delai', $demande->delai, FALSE) === NULL)
                echo ('<div id="date_souhaitee_select" style="display: none;">');
            else
                echo ('<div id="date_souhaitee_select">');

            echo form_label('<u>Date souhaitée pour la réalisation : </u>');
            echo form_label('<i>( si vous n\'avez pas accès au calendrier, utilisez le format suivant : AAAA-MM-JJ )</i>', '', array('style' => 'font-size:14px;'));

            $date = array(
                'name' => 'date_souhaitee',
                'id' => 'date_souhaitee',
                'value' => set_value('date_souhaitee', $demande->date_souhaitee_form, FALSE),
                'type' => 'date',
                'placeholder' => 'AAAA-MM-JJ',
                'class' => 'form-control'
            );
            echo form_input($date).'<br></div>';

            $modif_delai = array(
                'name' => 'maj',
                'value' => 'Modifier le délai',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($modif_delai);
        }
        echo form_close();
        ?>
            </div>
        </div>
    </div>
</section>