<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">


    <?php echo validation_errors() . '<br><br>'; ?>


    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    INSCRIPTION
                </h2>
            </div>
        </div>
        <br/>
        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">


                <?php

                echo $error;

                echo form_open('login/inscrire', array('class' => 'mbr-form', 'autocomplete' => 'off'));

                $attributes1 = array('style' => 'font-size:1.5em; font-weight: bold;');
                $attributes2 = array('style' => 'font-size:0.8em; font-style: italic;');
                $attributes3 = array('style' => 'font-size:1em; text-decoration: underline;');

                echo form_label('Données personnelles', '', $attributes1).'<br><br>';

                $pole_attribut = array('class' => 'form-control', 'id' => 'choix_pole');
                echo form_label('Votre appartenance', '', $attributes3);
                echo form_dropdown('pole', html_escape($pole), set_value('pole'), $pole_attribut).'<br>';

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
                    'class' => 'form-control'
                );
                echo form_password($password).'<br>';

                $passwordConfirm = array(
                    'name' => 'passwordConfirm',
                    'placeholder' => 'Confirmer le Mot de passe',
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_password($passwordConfirm).'<br><br>';

                echo form_label('Finaliser l\'inscription', '', $attributes1).'<br><br>';

                if ( ! empty($google_recaptcha) ) echo '<div class="g-recaptcha" data-sitekey="'.$google_recaptcha.'"></div>';
                else echo '<div id="ci_captcha" onload="doubleSizeImage()">'.$ci_captcha . '<br><br>' . form_input(array('name' => 'ci_captcha', 'class' => 'form-control', 'placeholder' => 'Recopiez les caractères ci-dessus, de gauche à droite', 'required' => TRUE, 'size' => 8)).'</div>';

                if ( ! empty($google_recaptcha) ) echo form_label('Veuillez attendre la validation du captcha avant de finaliser l\'inscription.', '', array('style' => 'font-weight: bold; color: red;'));
                
                echo '<br><br>';

                $connection = array(
                    'name' => 'connection',
                    'value' => 'S\'inscrire',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );
                echo form_submit($connection);

                echo form_close();
                ?>

                <script>
                    function doubleSizeImage() {
                        var element = document.getElementById('ci_captcha');

                        if (element == null) return;

                        var dblWdth = element.children[0].width * 2;
                        var dblHt = element.children[0].height * 2;
                        element.children[0].width = dblWdth;
                        element.children[0].height = dblHt;
                    }
                    window.onload = doubleSizeImage;
                </script>

            </div>
        </div>
    </div>
</section>