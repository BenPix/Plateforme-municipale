<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>

  <!-- Site made with Mobirise Website Builder v4.8.1, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="ROBOTS" content="noindex">
  <link rel="shortcut icon" href="<?php echo img_url($logo_icone) ?>" type="image/x-icon">
  <title><?php echo $titre . '  ♦ ' . $commune ?></title>
  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo css_url('tether/tether.min') ?>">
  <link rel="stylesheet" href="<?php echo css_url('bootstrap/css/bootstrap.min') ?>">
  <link rel="stylesheet" href="<?php echo css_url('bootstrap/css/bootstrap-grid.min') ?>">
  <link rel="stylesheet" href="<?php echo css_url('bootstrap/css/bootstrap-reboot.min') ?>">
  <link rel="stylesheet" href="<?php echo css_url('theme/css/style') ?>">
  <link rel="stylesheet" href="<?php echo css_url('mobirise/css/mbr-additional') ?>" type="text/css">
  <link rel="stylesheet" href="<?php echo css_url('css/style') ?>">
  <!-- google font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  
  
</head>
<body>
  <!-- google recaptcha -->
  <script src='https://www.google.com/recaptcha/api.js?hl=fr'></script>
  <section class="mbr-section content4 cid-r1fWA7j0DW" id="content4-b">



    <div class="container">
      <div class="media-container-row">
        <a href="<?php echo site_url('login')?>"><img src="<?php echo img_url($logo_entete) ?>" alt="" style="height: 5.8rem;"></a>
        <div class="title col-12 col-md-8">
          <h2 class="align-center pb-3 mbr-fonts-style display-1"><strong><?php echo $commune_uppercase ?></strong></h2>



        </div>
      </div>
    </div>
  </section>