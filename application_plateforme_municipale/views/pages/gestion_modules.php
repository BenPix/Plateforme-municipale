<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">
    <div class="container">
        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    GESTION DES MODULES
                </h2>
                <br>
                <?php
                echo validation_errors();

                echo form_open('gestion/modules', 'class="mbr-form"');

                echo form_label('<u>Modules :</u>').'<br>';
                foreach ($modules as $row) {

                    $data = array(
                        'name' => 'modules[]',
                        'value' => $row->id,
                        'checked' => set_checkbox('modules[]', $row->id, in_array($row->id, $activated_modules))
                    );
                    echo '<label class="container-of-checkbox" title="Module "><strong>'.ucfirst($row->module).'</strong>';
                    echo form_checkbox($data);
                    echo '<span class="mark-of-checkbox"></span>';
                    echo '</label>';
                }
                echo '<br>';

                $modifier = array(
                    'name' => 'modifier',
                    'value' => 'Modifier',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );
                echo form_submit($modifier);
                echo form_close();
                ?>

            </div>
        </div>
    </div>
</section>