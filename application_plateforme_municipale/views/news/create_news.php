<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">


<!--link de la textbox-->
<script class="jsbin" type="text/javascript" src="<?php echo js_url('textboxio/googleapis/ajax/libs/jquery/jquery.min') ?>"></script>
<script class="jsbin" type="text/javascript" src="<?php echo js_url('textboxio/googleapis/ajax/libs/jquery/jquery-ui.min') ?>"></script>
<!--link de la textbox-->


    <div class="container">
        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    CRÉATION D'ARTICLE
                </h2>
                <br>
                <?php
                echo validation_errors();
                echo $error;
                ?>
                <br>

                <!-- formulaire de création d'article -->

                <img id="blah" src="#" alt="image de l'article" style="display:none" />

                <?php
                echo form_open_multipart('accueil/publier_news', 'class="mbr-form"');

                echo form_label('<u>Image de l\'article :</u> (facultatif)').'<br>';
                echo form_label('<span style="font-size: 12px;">Si vous ne choisissez pas d\'image, une image par défaut sera utilisée.</span>').'<br>';
                echo form_label('<span style="font-size: 12px;">Taille max : 1 Mo / Formats acceptés : png | jpeg | jpg</span>').'<br>';
                echo form_upload(array('accept' => '.png,.jpg,.jpeg','type'=>'button', 'name'=>'image_article', 'onchange'=>'readURL(this);')).'<br><br>';

                echo form_label('<u>Titre de l\'article :</u>').'<br>';
                echo form_input(array('name'=>'titre','value'=>set_value('titre'),'required'=>'','class'=>'form-control')).'<br>';

                echo form_label('<u>Description de l\'article :</u>').'<br>';
                echo form_label('<span style="font-size: 12px;">La description n\'apparaît que sur la page d\'accueil.</span>').'<br>';
                echo form_textarea(array('name'=>'description','value'=>set_value('description'),'required'=>'','class'=>'form-control')).'<br>';

                echo form_label('<u>Contenu de l\'article :</u>').'<br>';
                echo form_label('<span style="font-size: 12px;">Vous pouvez agrémenter le contenu de l\'article avec des images, en les glissant simplement au bon endroit dans la textbox ci-dessous.</span>').'<br>';

                ?>
                <textarea name="contenu" id="mytextarea" rows="10"><?php echo set_value('contenu'); ?></textarea><br>
                <?php

                echo form_label('<u>Date de suppression de l\'article :</u>').'<br>';
                echo form_label('<i>( si vous n\'avez pas accès au calendrier, utilisez le format suivant : AAAA-MM-JJ )</i>', '', array('style' => 'font-size:14px;'));
                $date_suppression = array(
                    'type' => 'date',
                    'required' => '',
                    'name' => 'date_suppression',
                    'value' => set_value('date_suppression'),
                    'class' => 'form-control'
                );
                echo form_input($date_suppression).'<br>';

                echo form_label('<u>Affecter l\'article aux catégories :</u>').'<br>';
                foreach ($categories as $row) {

                    $data = array(
                        'name' => 'categorie[]',
                        'value' => $row->id,
                        'checked' => set_checkbox('categorie[]', $row->id)
                    );
                    echo '<label class="container-of-checkbox" title="Destiné aux '.htmlspecialchars($row->cible).'"><strong>'.htmlspecialchars($row->nom).'</strong>';
                    echo form_checkbox($data);
                    echo '<span class="mark-of-checkbox"></span>';
                    echo '</label>';
                }
                echo '<br>';

                $publier = array(
                    'name' => 'publier',
                    'value' => 'Publier',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );
                echo form_submit($publier);
                echo form_close();
                ?>
  
                <script>
                    var config = {
                        spelling : {
                            autocorrect : true
                        },
                        autosubmit: true,
                        css : {
                            stylesheets : [''],
                            styles : [               
                                { rule: 'p',    text: 'Paragraphe' },
                                { rule: 'h1',   text: 'Titre 1' },
                                { rule: 'h2',   text: 'Titre 2' },
                                { rule: 'h3',   text: 'Titre 3' },
                                { rule: 'h4',   text: 'Titre 4' }
                            ]
                        },
                        codeview : {
                            enabled: true,
                            showButton: true
                        },
                        images : {
                            allowLocal : true
                        },
                        languages : ['fr'],
                        macros : {
                            allowed : [ 'headings', 'lists', 'semantics', 'entities', 'hr', 'link' ]
                        },
                        ui : {
                            local : 'fr',
                            toolbar :  {
                                items : [
                                    {
                                        label : 'group.undo',
                                        items : [ 'undo', 'redo' ]
                                    },
                                    {
                                        label : 'group.insert',
                                        items : [
                                            {
                                                id : 'insert',
                                                label : 'Menu Insertion',
                                                items : [ 'link', 'specialchar', 'hr' ]
                                            }
                                        ]
                                    },
                                    {
                                        label : 'group.style',
                                        items : [ 'styles' ]
                                    },
                                    {
                                        label : 'group.emphasis',
                                        items : [ 'bold', 'italic', 'underline' ]
                                    },
                                    {
                                        label : 'group.align',
                                        items : [ 'alignment' ]
                                    },
                                    {
                                        label : 'group.listindent',
                                        items : [ 'ul', 'ol', 'indent', 'outdent', 'blockquote' ]
                                    },
                                    {
                                        label : 'group.format',
                                        items : [ 'font-menu', 'removeformat' ]
                                    },
                                    {
                                        label : 'group.tools',
                                        items : [ 'fullscreen', 'usersettings' ]
                                    }
                                ]
                            }
                        }
                    };

                    textboxio.replace('#mytextarea', config);
                </script>
                <script>
                    function readURL(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();

                            reader.onload = function (e) {
                                $('#blah')
                                    .attr('src', e.target.result)
                                    .attr('style', 'display:block')
                                    .width(600);
                            };

                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                </script>

            </div>
        </div>
    </div>
</section>