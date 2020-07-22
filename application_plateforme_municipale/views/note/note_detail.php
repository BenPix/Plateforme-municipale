
<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

    <div>
        <h4 style="text-align: center;"><u>DÉTAILS DE LA NOTE N°<?= $note->id ?></u></h4>

        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">
                <br>
                <br>
        <?php 
        /*
        *
        *
        *
        *
        *
        * PDF
        *
        *
        *
        *
        *
        */
        if ($may_see_pdf) {
            $attribut_lien = array('class' => 'btn btn-primary display-4', 'target' => '_blanck');
            echo '<div style="text-align: center;">';
            echo anchor('note/download_pdf/'.$note->id, 'OUVRIR LE FORMAT PDF', $attribut_lien).'<br><br><br>';
            echo '</div>';
        }
        /*
        *
        *
        *
        *
        *
        * CONTENU DE LA NOTE
        *
        *
        *
        *
        *
        */
        echo validation_errors();

        echo form_open('note/soumission/'.$note->id, 'class="mbr-form"');

        $objet_attr = array(
            'disabled' => TRUE,
            'value' => $note->objet,
            'class' => 'form-control'
        );
        echo form_label('<u>Objet de la note : </u>');
        echo form_input($objet_attr).'<br>';

        $note_attr = array(
            'disabled' => TRUE,
            'value' => $note->note,
            'class' => 'form-control',
            'rows' => $note->nombre_lignes
        );
        echo form_label('<u>Contenu de la note : </u>');
        echo form_textarea($note_attr).'<br>';

        if ($note->has_uploads) {
            echo form_label('<u>Pièces jointes : </u>');

            $button_attr = array(
                'class' => 'form-control',
                'id' => 'dl-note'
            );
            $button_title = 'TÉLÉCHARGER ';
            $button_title .= $note->nombre_uploads > 1 ? 'LES '.$note->nombre_uploads.' PIÈCES JOINTES' : 'LA PIÈCE JOINTE';

            $button = form_button($button_attr, $button_title);
            echo anchor('note/download_pj/'.$note->id, $button);
        }

        ?>
        <style>
            #dl-note:hover
            {
                background-color: #ddd;
                cursor: pointer;
                color: #03a6c9;
            }
        </style>
        <?php

        echo '<br><br>';
        echo '<p><strong>Note rédigée par '.htmlspecialchars($note->redacteur).' le '.$note->horodateur.'.</strong></p>';
        /*
        *
        *
        *
        *
        *
        * TABLEAU DES VALIDATIONS
        *
        *
        *
        *
        *
        */
        ?>

        <br>
        <hr color="#aaa">
        <br>
        <br>
        <h4 style="text-align: center;"><u>CHAINE DE VALIDATIONS</u></h4>
        <br>
        <br>

        <?php

        // affichage du tableau
        $this->table->set_heading($heading);

        $tmpl = array(
            'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab">',
            'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
        );

        $this->table->set_template($tmpl);

        $this->table->function = 'htmlspecialchars';

        $this->table->set_columns_for_callback_function(array(1));

        echo $this->table->generate($table).'<br><br><hr color="#aaa"><br><br>';
        /*
        *
        *
        *
        *
        *
        * AJOUT D'UN VALIDEUR
        *
        *
        *
        *
        *
        */
        if ($may_add_new_validator && ! empty($users) ) {
            echo form_label('<u>Ajouter un valideur dans la chaîne de validation de cette note : </u>');
            echo form_dropdown('valideur_id', html_escape($users), set_value('valideur_id'), array('class' => 'form-control')).'<br>';

            echo form_label('<u>Positionner ce valideur à l\'étape suivante : </u>');
            echo form_dropdown('etape', html_escape($allowed_steps), set_value('etape'), array('class' => 'form-control')).'<br>';

            $ajouter_attr = array(
                'name' => 'soumission',
                'value' => 'Ajouter',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($ajouter_attr);

            echo '<br><br><hr color="#aaa"><br><br>';
        }
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
        if (count($note->commentaires) > 0) echo '<h4 style="text-align: center;"><u>COMMENTAIRES</u></h4><br><br>';

        // boucle <input> avec les commentaires
        foreach ($note->commentaires as $row) {
            echo 'Posté par <strong>'.htmlspecialchars($row->utilisateur).'</strong> le '.$row->horodateur.'<br>';
            $comms_attr = array(
                'value' => $row->commentaire,
                'disabled' => '',
                'class' => 'form-control',
                'rows' => $row->nombre_lignes
            );
            echo form_textarea($comms_attr).'<br>';
        }

        if ($may_comment) {

            echo form_label('<u>Vous pouvez laisser un commentaire : </u>').'<br>';
            echo form_label('<span style="font-size: 12px;">( Champ obligatoire si vous refusez cette note )</span>');
            $commentaire_attr = array(
                'name' => 'commentaire',
                'value' => set_value('commentaire'),
                'class' => 'form-control',
                'rows' => '4',
                'cols' => '40'
            );
            echo form_textarea($commentaire_attr).'<br><br>';

            $commenter = array(
                'name' => 'soumission',
                'value' => 'Poster le commentaire' . ($may_validate ? ' (sans valider ni refuser)' : ''),
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
        * VALIDATION
        *
        *
        *
        *
        *
        */
        if ($may_validate) {

            echo '<hr><br><br><h4 style="text-align: center;"><u>VALIDATION OU REFUS</u></h4><br><br>';

            echo form_label('Si vous avez saisi un commentaire dans le champ correspondant, il sera enregistré en même temps que la validation.').'<br>';
            echo form_label('Si vous <u>refusez</u>, le champ commentaire est <u>obligatoire</u>, pour expliquer la raison du refus.').'<br><br>';
            
            $valider = array(
                'name' => 'soumission',
                'value' => 'Valider',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4'
            );
            echo form_submit($valider);

            $refuser = array(
                'name' => 'soumission',
                'value' => 'Refuser',
                'type' => 'submit',
                'class' => 'btn btn-primary  display-4',
                'style' => 'float:right'
            );
            echo form_submit($refuser).'<br><br>';
        }
        /*
        *
        *
        *
        *
        *
        * PDF
        *
        *
        *
        *
        *
        */
        if ($may_see_pdf) {
            echo '<br><div style="text-align: center;">';
            echo anchor('note/download_pdf/'.$note->id, 'OUVRIR LE FORMAT PDF', $attribut_lien).'<br>';
            echo '</div>';
        }

        echo form_close();

        if (NULL !== $this->session->flashdata('note_section') && NULL !== $this->session->flashdata('note_page')) {
            echo '<br><br><hr><br><br>';
            $attribut_retour = array('class' => 'btn btn-primary display-4');
            echo anchor('note/'.$this->session->flashdata('note_section').'/'.$this->session->flashdata('note_page'), 'Retour', $attribut_retour).'<br>';
        }
        ?>


            </div>
        </div>
    </div>
</section>