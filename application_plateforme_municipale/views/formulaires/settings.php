<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">


    <?php echo validation_errors() . '<br><br>' . $error; ?>


    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 style="font-size: 2em; ;" class="align-center pb-2 mbr-fonts-style display-2">
                    CRÉATION DU COMPTE ADMIN
                </h2>
            </div>
        </div>
        <br/>
        <div class="row py-2 justify-content-center">
            <div style="background-color: lightgray;" class="col-12 col-lg-6  col-md-8 ">


                <?php
                
                echo form_open_multipart('settings/start', array('class' => 'mbr-form', 'autocomplete' => 'off'));

                $attributes1 = array('style' => 'font-size:1.5em; font-weight: bold;');
                $attributes2 = array('style' => 'font-size:0.8em; font-style: italic;');
                $attributes3 = array('style' => 'font-size:1em; text-decoration: underline;');

                echo form_label('Données personnelles', '', $attributes1).'<br><br>';

                $nom = array(
                    'name' => 'nom',
                    'placeholder' => 'Nom',
                    'value' => set_value('nom'),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_input($nom).'<br>';

                $prenom = array(
                    'name' => 'prenom',
                    'placeholder' => 'Prénom',
                    'value' => set_value('prenom'),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_input($prenom).'<br>';

                $email = array(
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => 'Email',
                    'value' => set_value('email'),
                    'required' => '',
                    'class' => 'form-control',
                    'id' => 'email-form3-2'
                );
                echo form_input($email).'<br><br>';

                echo form_label('Données de connexion', '', $attributes1);
                echo form_label('Choisissez un Pseudonyme et un mot de passe qui serviront à vous identifier lors de la connexion', '', $attributes3);
                echo form_label('(le mot de passe doit avoir une longueur de 9 caractère minimum)', '', $attributes2).'<br><br>';

                $pseudo = array(
                    'name' => 'pseudo',
                    'placeholder' => 'Pseudonyme',
                    'value' => set_value('pseudo'),
                    'required' => '',
                    'class' => 'form-control'
                );

                echo form_input($pseudo).'<br>';

                $password = array(
                    'name' => 'password',
                    'placeholder' => 'Mot de passe',
                    'required' => '',
                    'minlength' => 9,
                    'class' => 'form-control'
                );
                echo form_password($password).'<br>';

                $passwordConfirm = array(
                    'name' => 'password_confirm',
                    'placeholder' => 'Confirmer le Mot de passe',
                    'required' => '',
                    'minlength' => 9,
                    'class' => 'form-control'
                );
                echo form_password($passwordConfirm).'<br><br>';

                ?>

                </div>
            </div>
            <br/><br/><br/>
            <div class="row justify-content-center">
                <div class="title col-12 col-lg-8">
                    <h2 style="font-size: 2em; ;" class="align-center pb-2 mbr-fonts-style display-2">
                        PARAMÉTRAGE GÉNÉRAL
                    </h2>
                </div>
            </div>
            <br/>
            <div class="row py-2 justify-content-center">
                <div style="background-color: lightgray;" class="col-12 col-lg-6  col-md-8 ">

                <?php

                echo form_label('Nom de la ville', '', $attributes1);
                echo form_label('Enregistrez le nom de la ville. Il sera affiché en entête des pages, et comme titre des pages de navigation.').'<br><br>';

                $commune = array(
                    'name' => 'commune',
                    'placeholder' => 'Nom de la Ville',
                    'value' => set_value('commune'),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_input($commune).'<br>';

                echo form_label('Logo de la ville', '', $attributes1);
                echo form_label('Importez le logo de la ville. Il sera affiché en entête des pages, et sera l\'icône des pages de navigation.').'<br><br>';

                echo form_label('Logo pour l\'entête', '', $attributes3);
                echo form_label('(la taille de l\'image doit être réduite au maximum pour améliorer la rapidité de navigation)', '', $attributes2).'<br><br>';

                $logo_entete = array(
                    'name' => 'logo_entete',
                    'accept' => '.jpg,.jpeg,.png',
                    'class' => 'form-control'
                );
                echo form_upload($logo_entete).'<br>';
                echo form_label('Taille Max. 100Ko', '', $attributes2).'<br>';
                echo form_label('Formats autorisé : jpg | jpeg | png', '', $attributes2).'<br><br>';


                echo form_label('Logo pour l\'icône de la page de navigation', '', $attributes3);
                echo form_label('(la taille de l\'image doit être réduite au maximum pour améliorer la rapidité de navigation)', '', $attributes2).'<br><br>';

                $logo_icone = array(
                    'name' => 'logo_icone',
                    'accept' => '.jpg,.jpeg,.png,.svg',
                    'class' => 'form-control'
                );
                echo form_upload($logo_icone).'<br>';
                echo form_label('Taille Max. 100Ko', '', $attributes2).'<br>';
                echo form_label('Formats autorisé : jpg | jpeg | png | svg', '', $attributes2).'<br><br><br>';


                echo form_label('Modules de l\'application', '', $attributes1);
                echo form_label('Choisissez les modules que vous souhaitez utiliser.');
                echo form_label('(Il sera toujours possible d\'activer ou de désactiver ces modules ultérieurement)', '', $attributes2).'<br><br>';

                $modules_attributes = array(
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_label('Modules', '', $attributes3).'<br>';
                echo form_label('(Pour sélectionner plusieurs modules dans la liste, maintenez la touche CTRL en cliquant)', '', $attributes2).'<br>';
                echo form_multiselect('modules[]', $modules, '', $modules_attributes).'<br>';

                ?>

                </div>
            </div>
            <br/><br/><br/>
            <div class="row justify-content-center">
                <div class="title col-12 col-lg-8">
                    <h2 style="font-size: 2em; ;" class="align-center pb-2 mbr-fonts-style display-2">
                        FINALISATION
                    </h2>
                </div>
            </div>
            <br/>
            <div class="row py-2 justify-content-center">
                <div style="background-color: lightgray;" class="col-12 col-lg-6  col-md-8 ">

                <?php

                echo form_label('Finaliser l\'enregistrement de toutes les données', '', $attributes1);
                echo form_label('En finalisant l\'enregistrement des données saisies, la base de données du site sera créée, le ou les modules seront installés, les logos seront enregistrés, et votre compte admin sera créé. Vous pourrez vous connecter à la plateforme immédiatement, et configurer le site à votre guise.').'<br><br>';

                $finaliser = array(
                    'name' => 'finaliser',
                    'value' => 'Finaliser',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );
                echo form_submit($finaliser);

                echo form_close();
                ?>

            </div>
        </div>
    </div>
</section>