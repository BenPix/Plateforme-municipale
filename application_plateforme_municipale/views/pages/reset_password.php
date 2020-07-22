<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">





    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    MOT DE PASSE OUBLIÉ
                </h2>
            </div>
        </div>

        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">

                <!-- formulaire de login -->

                <?php

                echo validation_errors();

                echo form_open('login/reset_password', 'class="mbr-form"');

                echo form_label('Un email vous sera envoyé pour créer votre nouveau mot de passe.');

                echo '<br><br>';

                echo '<div class="error">'.$error.'</div><br>';

                $pseudo = array(
                    'name' => 'pseudo',
                    'placeholder' => 'Votre pseudonyme / identifiant',
                    'value' => set_value('pseudo'),
                    'required' => '',
                    'class' => 'form-control'
                );
                echo form_input($pseudo).'<br>';

                $email = array(
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => 'Votre adresse email',
                    'value' => set_value('email'),
                    'required' => '',
                    'class' => 'form-control',
                    'id' => 'email-form3-2'
                );
                echo form_input($email).'<br>';

                $envoyer = array(
                    'name' => 'envoyer',
                    'value' => 'Envoyer l\'email',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );

                echo form_submit($envoyer);

                echo form_close();
                ?>

            </div>
        </div>
    </div>
</section>