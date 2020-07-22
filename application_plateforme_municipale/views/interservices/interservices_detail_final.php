
<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

    <div>
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
        if (!empty($demande->pieces_jointes)) echo form_label('<u>Télécharger les pièces jointes : </u><br>'.$demande->pieces_jointes).'<br>';

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
        if (!empty($commentaires)) echo '<br><br><p style="font-size: 20px;text-align: center;"><u>COMMENTAIRES</u></p>';

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
        ?>
            </div>
        </div>
    </div>
</section>