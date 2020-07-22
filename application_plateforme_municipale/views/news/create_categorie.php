<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

    <div>
        <p style="font-size: 20px;text-align: center;">
            <u>CATÉGORIES</u>
        </p>
        <?php
        $this->table->set_heading($headings);

        
        $tmpl = array(
            'table_open' => '<table border="solid"; cellpadding="4" cellspacing="0" class="tab" id="tab">',
            'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
        );

        $this->table->set_template($tmpl);

        echo $this->table->generate($tableau);
        ?>
    </div>
    <br><br>

    <div>
        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">

                <?php
                echo validation_errors();

                echo form_open('accueil/create_categorie');

                $attributes1 = array(
                    'style' => 'font-size:24px;'
                );
                echo form_label('<strong>Créer une nouvelle catégorie</strong>', '', $attributes1).'<br>';

                $categorie = array(
                    'name' => 'categorie',
                    'placeholder' => 'Nom',
                    'value' => set_value('categorie', '', FALSE),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_label('<u>Nom de la catégorie : </u>');
                echo form_input($categorie).'<br>';

                $cible = array(
                    'name' => 'cible',
                    'placeholder' => 'ex. associations, élus de l\'opposition, ...',
                    'value' => set_value('cible', '', FALSE),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_label('<u>Cette catégorie est destinée aux : </u>');
                echo form_input($cible).'<br>';

                $valider = array(
                    'name' => 'confirmation',
                    'value' => 'Créer',
                    'class' => 'btn btn-primary  display-4',
                );
                echo form_submit($valider).'<br>';

                echo form_close();
                ?>
            </div>
        </div>
    </div>
</section>