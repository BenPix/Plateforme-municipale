<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>

  <!-- Site made with Mobirise Website Builder v4.8.1, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
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
  <!-- css du menu -->
  <link rel="stylesheet" href="<?php echo css_url('template_news/mon_style_header') ?>">
  <!--lien pour utiliser la police source sans pro (police du menu) -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
  <!-- google font -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- jquery script -->
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <!-- script pour la textbox.io -->
  <script type="text/javascript" src="<?php echo js_url('textboxio/textboxio') ?>"></script>
  
</head>


<body>
  <!--menu horizontal-->
<div class="container-topnav">
  <div class="topnav" id="myTopnav">
    <?php echo $menu_personnalise ?> 
    <div style="display:relative;"><a href="javascript:void(0);" style="font-size:15px;" class="icon" onclick="myFunctionSains()">&#9776;</a></div>  
  </div>
</div>


<script>
function myFunctionSains() {
  var x = document.getElementById("myTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}
</script>
<!--menu horizontal-->

<!--bannière avec logo-->
<div id="brand-row">
<a href="<?php echo site_url().'/accueil'?>" class="custom-logo-link" rel="home" itemprop="url"><img max-width="349" height="192" src="<?php echo img_url($logo_entete) ?>" class="custom-logo" alt="Logo Sains-en-Gohelle" itemprop="logo" srcset="<?php echo img_url($logo_entete) ?> 349w, <?php echo img_url($logo_entete) ?> 300w" sizes="(max-width: 349px) 100vw, 349px" /></a> </div>
<!--bannière avec logo-->
