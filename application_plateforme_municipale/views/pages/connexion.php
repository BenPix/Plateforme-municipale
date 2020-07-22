<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

<!-- script de détection du navigateur pour informer sur la compatibilité -->
<script type="text/javascript">
    window.onload = function showForIE() {
        // Opera 8.0+
        var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

        // Firefox 1.0+
        var isFirefox = typeof InstallTrigger !== 'undefined';

        // Safari 3.0+ "[object HTMLElementConstructor]" 
        var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));

        // Internet Explorer 6-11
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        // Edge 20+
        var isEdge = !isIE && !!window.StyleMedia;

        // Chrome 1 - 71
        var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);

        // Blink engine detection
        var isBlink = (isChrome || isOpera) && !!window.CSS;

        // la div a afficher
        var myDiv = document.getElementById('IE');

        if (isIE) {
            myDiv.style.display = 'block';
        }
    }
</script>

    <div class="container">
        <div class="row justify-content-center">

            <div id="IE" style="border: solid red 3px; width: 60%; padding: 10px; margin-bottom: 50px; display: none">
                <p>Il semblerait que vous utilisez <strong>Internet Explorer</strong> comme navigateur.</p>
                <p>Pour une utilisation optimale du site, nous vous suggérons de choisir un autre navigateur. Nous vous conseillons <strong>Chrome</strong> étant donné que le site a été conçu pour fonctionner parfaitement avec ce navigateur.</p>
                <p>Pour télécharger Chrome, cliquez sur ce <a href="https://www.google.com/chrome/" target="_blank">lien</a>.</p>
            </div>
            
            <div class="title col-12 col-lg-8">
                <h2 class="align-center pb-2 mbr-fonts-style display-2">
                    IDENTIFICATION
                </h2>
            </div>
        </div>

        <div class="row py-2 justify-content-center">
            <div class="col-12 col-lg-6  col-md-8 ">

                <!-- formulaire de login -->

                 <?php

                echo validation_errors();
                echo $error;

                echo form_open('login/check', 'class="mbr-form"');

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
                    'value' => set_value('password'),
                    'required' => '',
                    'class' => 'form-control',
                    'style' => 'margin-bottom:10px;',
                );
                echo form_password($password);

                echo anchor('login/mot_de_passe_oublie', 'Mot de passe oublié ?', 'style="font-size: 12px;"').'<br>';

                $connection = array(
                    'name' => 'connection',
                    'value' => 'Se connecter',
                    'type' => 'submit',
                    'class' => 'btn btn-primary  display-4'
                );

                echo form_submit($connection);

                echo form_close();
                ?>

                ou, si vous n'êtes pas encore inscrit :
                <br>

                <a href="<?php echo site_url('login/inscription'); ?>"><button class="btn btn-primary  display-4">S'inscrire</button></a>
            </div>
        </div>
    </div>
</section>