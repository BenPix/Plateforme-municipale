
<div class="row py-2 justify-content-center">
    <div class="col-12 col-lg-6  col-md-8 ">
        <br>

        <?php 
        echo validation_errors();
        echo $error;
        
        echo form_fieldset('<u>Note à transmettre</u>');
        echo '<br>';

        echo form_open_multipart('note/creation_go', 'class="mbr-form"');

        $objet = array(
            'name' => 'objet',
            'value' => set_value('objet', '', FALSE),
            'placeholder' => 'Objet de la note',
            'required' => '',
            'class' => 'form-control'
        );
        echo form_input($objet).'<br>';

        $textarea = array(
            'name' => 'note',
            'value' => set_value('note', '', FALSE),
            'placeholder' => 'Votre note',
            'required' => '',
            'class' => 'form-control',
            'rows' => '10',
            'cols' => '5'
        );
        echo form_textarea($textarea).'<br>';

        echo form_label('<u>Pièce jointe n°1 : </u>').'<br>';
        $file1 = array('name' => 'userfile1', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file1).'<br>';

        echo form_label('<u>Pièce jointe n°2 : </u>').'<br>';
        $file2 = array('name' => 'userfile2', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file2).'<br>';

        echo form_label('<u>Pièce jointe n°3 : </u>').'<br>';
        $file3 = array('name' => 'userfile3', 'class' => 'form-control', 'accept' => '.pdf,.jpeg,.jpg,.png,.gif,.txt,.doc,.docx,.xls,.xlsx');
        echo form_upload($file3);
        echo form_label('Pour chaque pièce jointe :<br>Formats autorisés : gif/jpg/jpeg/png/pdf/txt/doc/docx/xls/xlsx<br>Poids max : 3Mo', '', array('style' => 'font-size: 11px')).'<br><br>';

        echo heading('<u>Par qui souhaitez-vous faire valider cette note ?</u>', 5).'<br>';
        echo form_label('<u>Valideur :</u>').'<br>';
        echo form_dropdown('workflow[]', $users, '', array('class' => 'form-control')).'<br>';

        echo '<button class="btn display-4" type="button" id="cloneSelect" style="color: white; background-color: green; border-color: green;" onmouseover="this.style.background=\'#004300\'" onmouseout="this.style.background=\'green\'">Ajouter un utilisateur à la chaîne de validation</button>'.'<br><br>';

        echo heading('<u>Enregistrer la note et enclencher la chaîne de validation</u>', 5).'<br>';

        $envoyer = array(
            'name' => 'envoyer',
            'value' => 'Envoyer la note',
            'type' => 'submit',
            'class' => 'btn btn-primary display-4'
        );
        echo form_submit($envoyer);

        echo form_close();

        echo form_fieldset_close();
        ?>
        <script>
            $('#cloneSelect').click(function() {
                //var select = $('select[name^="workflow"]:last');
                var select = $('select[name^="workflow"]:last');
                //var num = parseInt( select.prop("name").match(/\d+/g), 10 ) +1;
                //var workflow = select.clone().prop('name', 'workflow'+num );
                var workflow = select.clone();
                //select.after( workflow.text('workflow'+num) );
                select.after( workflow.text('workflow[]') );
                select.after( '<br />');
                select.after( '<label><u>Valideur suivant :</u></label>');
                select.after( '<br />');
                workflow.html(select.html());
            });
        </script>
        <br>
    </div>
</div>