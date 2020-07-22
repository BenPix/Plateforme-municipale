
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


                </script><br>


        <?php 
        echo validation_errors();
        echo $error;
        
        echo form_fieldset('<u>Demande interservices</u>');
        echo heading('Attention : Si votre demande contient plusieurs requêtes, il est préférable de créer une demande par requête.', 6, array('style' => 'color:red;'));
        echo '<br>';

        echo form_open_multipart('demande/demande_interservices_traitement', 'class="mbr-form"');

        echo form_label('<u>Direction sollicitée : </u>').'<br>';
        echo '<p style="font-size: 0.8em;">( Vous pouvez sélectionner plusieurs Pôles en maintenant la touche CTRL, ce qui va dupliquer la demande )</p>';
        echo form_multiselect('direction_sollicitee[]', html_escape($pole), set_value('direction_sollicitee'), array('class' => 'form-control', 'required' => TRUE)).'<br>';

        $textarea = array(
            'name' => 'demande',
            'value' => set_value('demande', '', FALSE),
            'placeholder' => 'Votre demande',
            'required' => '',
            'class' => 'form-control',
            'rows' => '10',
            'cols' => '5'
        );
        echo form_textarea($textarea).'<br>';

        $delai = array(
            'au mieux' => 'Au mieux',
            'délai maximum' => 'Délai maximum',
            'date précise' => 'Date précise'
        );
        echo form_label('<u>Délai : </u>').'<br>';
        echo form_dropdown('delai', $delai, set_value('delai', 'au mieux', FALSE), array('class' => 'form-control')).'<br>';

        if (set_value('delai') == 'au mieux' || set_value('delai') == NULL)
            echo ('<div id="date_souhaitee_select" style="display: none;">');
        else
            echo ('<div id="date_souhaitee_select">');

        $attributes2 = array(
                    'style' => 'font-size:14px;'
                );
        echo form_label('<u>Date souhaitée pour la réalisation : </u>');
        echo form_label('<i>( si vous n\'avez pas accès au calendrier, utilisez le format suivant : AAAA-MM-JJ )</i>', '', $attributes2);
        $date = array(
            'name' => 'date_souhaitee',
            'id' => 'date_souhaitee',
            'value' => set_value('date_souhaitee'),
            'type' => 'date',
            'placeholder' => 'AAAA-MM-JJ',
            'class' => 'form-control'
        );
        echo form_input($date).'<br>';

        ?>
        </div>
        <?php

        echo form_label('<u>Degré d\'urgence pour les interventions techniques : </u>').'<br>';

        $radio1 = array(
            'name'          => 'urgence',
            'value'         => '1',
            'checked'       => (set_value('urgence') == '1' || empty(set_value('urgence'))) ? TRUE : FALSE,
            'style'         => 'margin-left:10px'
        );
        $radio2 = array(
            'name'          => 'urgence',
            'value'         => '2',
            'checked'       => (set_value('urgence') == '2') ? TRUE : FALSE,
           'style'         => 'margin-left:40px'
        );
        $radio3 = array(
            'name'          => 'urgence',
            'value'         => '3',
            'checked'       => (set_value('urgence') == '3') ? TRUE : FALSE,
            'style'         => 'margin-left:40px'
        );
        $radio4 = array(
            'name'          => 'urgence',
            'value'         => '4',
            'checked'       => (set_value('urgence') == '4') ? TRUE : FALSE,
           'style'         => 'margin-left:40px'
        );
        $radio5 = array(
            'name'          => 'urgence',
            'value'         => '5',
            'checked'       => (set_value('urgence') == '5') ? TRUE : FALSE,
            'style'         => 'margin-left:40px;'
        );

        echo form_label('Pas d\'urgence');
        echo form_radio($radio1).'1';
        echo form_radio($radio2).'2';
        echo form_radio($radio3).'3';
        echo form_radio($radio4).'4';
        echo form_radio($radio5).'5';

        $label = array(
            'style'  => 'margin-left:10px;'
        );
        echo form_label('Très urgent', '', $label).'<br><br>';

        echo form_label('<u>Pièce jointe n°1 : </u>*').'<br>';
        $file1 = array('name' => 'userfile1', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file1).'<br>';

        echo form_label('<u>Pièce jointe n°2 : </u>*').'<br>';
        $file2 = array('name' => 'userfile2', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file2).'<br>';

        echo form_label('<u>Pièce jointe n°3 : </u>*').'<br>';
        $file3 = array('name' => 'userfile3', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file3);

        $valider = array(
            'name' => 'valider_demande',
            'value' => 'Envoyer la demande',
            'type' => 'submit',
            'class' => 'btn btn-primary  display-4'
        );
        $attribut = array('style' => 'font-size: 11px');
        echo form_label('* Formats autorisés : gif/jpg/jpeg/png/pdf/txt/doc/docx/xls/xlsx<br>Poids max : 8Mo', '', $attribut).'<br><br>';

        echo form_submit($valider);

        echo form_close();

        echo form_fieldset_close();


        ?>

    </div>
</div>