<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">





    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    NOUVEAU MOT DE PASSE
                </h2>
            </div>
        </div>

        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">

                <!-- formulaire de login -->

                <?php

                echo validation_errors();
                echo '<div class="error">'.$error.'</div><br>';

                $hidden = array('utilisateur_id' => $utilisateur_id, 'token' => $token);

                echo form_open('password/reset', 'class="mbr-form"', $hidden);

                $password = array(
                    'name' => 'password',
                    'placeholder' => 'Votre nouveau Mot de passe',
                    'required' => '',
                    'class' => 'form-control'
                );

                echo form_password($password).'<br>';

                $passwordConfirm = array(
                    'name' => 'password_confirm',
                    'placeholder' => 'Confirmer le nouveau Mot de passe',
                    'required' => '',
                    'class' => 'form-control'
                );

                echo form_password($passwordConfirm).'<br>';

                $valider = array(
                    'name' => 'connection',
                    'value' => 'Valider',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );

                echo form_submit($valider);

                echo form_close();
                ?>

            </div>
        </div>
    </div>
</section>