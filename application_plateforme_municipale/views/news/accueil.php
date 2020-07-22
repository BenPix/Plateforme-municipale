
<!doctype html>
<html>

<!--head-->
<head>
<meta charset="utf-8">
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $titre . ' ♦ Sains-en-Gohelle' ?></title>

<meta name="description" content="" />
<link rel="shortcut icon" href="<?php echo img_url($logo_icone) ?>" type="image/x-icon">

<!-- CSS -->
<link rel='stylesheet' id='bootstrap-css' href='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/css/bootstrap.min.css?ver=3.3.7' type='text/css' media='all' />
<link rel='stylesheet' id='font-awesome-css' href='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/css/font-awesome.min.css?ver=1.0' type='text/css' media='all' />
<link rel='stylesheet' id='slick-css' href='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/css/slick.css?ver=1.6.0' type='text/css' media='all' />
<link rel='stylesheet' id='underwood-pro-style-css' href='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/style.css?ver=4.8.8' type='text/css' media='all' />
<style id='kirki-styles-underwood-pro-inline-css' type='text/css'>
body{font-family:"Source Sans Pro", Helvetica, Arial, sans-serif;font-weight:200;font-style:normal;}blockquote,.banner-wrap h2,.banner-wrap h2 a,.blog-title a,.singular-related h4,.comments-title,#reply-title,.content-column-title,.sidebar .sidebar_widget > span.widget_title,.sidebar .sidebar_widget .featured_posts h4,.instagram-title,#footer-row h4,.singular-entry h1,.singular-entry h2, .singular-entry h3, .singular-entry h4, .singular-entry h5, .singular-entry h6{font-family:"Source Sans Pro", Helvetica, Arial, sans-serif;}.navbar ul.nav li a,input[type="button"],input[type="submit"],.more_tag span,button,.about_widget_link,.blog-next-prev a,.sidebar .sidebar_widget > span.widget_title,.comment-author,.comment-author a,.comment-date,#copyright-row p,#copyright-row a{font-family:"Source Sans Pro", Helvetica, Arial, sans-serif;}body,a,a:visited,a:hover,.blog-meta .auth a,.singular-entry .blog-title a,.singular-entry .blog-title a:hover, .blog-title a, .blog-title a:hover,.blog-cat,.blog-cat a,.sidebar .sidebar_widget > span.widget_title,.singular-entry span.social-icons a, .comment-body .comment-author .url, .comment-body .comment-reply-link, .singular-entry .tags-wrap a, .more_tag span a, .tag-sep,button#searchsubmit,button#searchsubmit:hover{color:#3a3a3a;}.more_tag span:hover{background:#3a3a3a;}.singular-entry a,.comment-body a{color:#1a95ff;}.singular-entry a:hover,.comment-body a:hover{color:#3a3a3a;}blockquote{color:#3a3a3a;border-color:#b1b1b1;}.singular-entry .blog-title a,.singular-entry .blog-title a:hover, .blog-title a, .blog-title a:hover{color:#3a3a3a;}a.dropdown-toggle:after, .navbar-default .navbar-nav>li>a, .navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover, .navbar-default .navbar-nav>.active>a, .navbar-default .navbar-nav>.active>a:focus, .navbar-default .navbar-nav>.active>a:hover,.navbar-default .navbar-nav>li>a,.navbar-default .navbar-nav>li>a:hover{color:#ffffff;}.navbar-default .navbar-nav>li>a:hover,.navbar-default .navbar-nav>.active>a:focus, .navbar-default .navbar-nav>.active>a:hover,.navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover{color:#ffffff;}.blog-item, .singular-entry, .saboxplugin-wrap, .singular-related, .comments-area,.sidebar .sidebar_widget,.singular-entry th{background-color:#ffffff;}#mainmenu-row{background-color:#333333;}#copyright-row, #footer-row, .instagram-pics li{background-color:#333333;}.slick-slide .banner-meta>div{background-color:rgba(255,255,255,.8);}input[type="button"],input[type="submit"],.more_tag span,button,.about_widget_link,.tags-wrap,.comment-wrap,textarea, input,.comment-img .avatar{border-color:#b1b1b1;}#mainmenu-row,.navbar ul.nav li ul li,.dropdown-menu{border-color:#b1b1b1;}
</style>
<!-- script -->
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-includes/js/jquery/jquery.js?ver=1.12.4'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/bootstrap.min.js?ver=3.3.7'></script>
<style type="text/css">
	.recentcomments a
	{
		display:inline !important;padding:0 !important;margin:0 !important;
	}
	.saboxplugin-authorname a
	{
		font-size:16px;font-weight:600;text-transform:uppercase;
	}
    .saboxplugin-wrap .saboxplugin-socials
    {
    	background-color: #ffffff;
    }
    .saboxplugin-wrap .saboxplugin-socials
    {
    	background: #FCFCFC; padding: 0 15px; -webkit-box-shadow: 0 1px 0 0 #eee inset; -moz-box-shadow: 0 1px 0 0 #eee inset; box-shadow: 0 1px 0 0 #eee inset;
    }
    @media (max-width: 640px)
    {
		#affichage-calendrier-personnel
		{
			display: none !important;
		}
	}
</style>

<!-- css du menu -->
<link rel="stylesheet" href="<?php echo css_url('template_news/mon_style_header') ?>">
<!--lien pour utiliser la police source sans pro (police du menu) -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
<!-- google font -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- FullCalendar -->
<link href='<?php echo css_url('fullcalendar/core/main') ?>' rel='stylesheet' />
<link href='<?php echo css_url('fullcalendar/daygrid/main') ?>' rel='stylesheet' />
<link href='<?php echo css_url('fullcalendar/timegrid/main') ?>' rel='stylesheet' />

<script src='<?php echo js_url('fullcalendar/core/main') ?>'></script>
<script src='<?php echo js_url('fullcalendar/core/locales/fr') ?>'></script>
<script src='<?php echo js_url('fullcalendar/daygrid/main') ?>'></script>
<script src='<?php echo js_url('fullcalendar/timegrid/main') ?>'></script>
<!-- FullCalendar -->

<!-- Lazyload with Yall.js (for loading inmages after scroll down to it only) -->
<script src="<?php echo js_url('yall/yall.min') ?>"></script>
<script>
  document.addEventListener("DOMContentLoaded", yall);
</script>
<!-- Lazyload with Yall.js -->

</head>
<!--head-->

<!--body-->
<body class="home blog wp-custom-logo frontpage-layout">

<!--menu horizontal-->
<div class="container-topnav">
  <div class="topnav" id="myTopnav">
    <?php echo $menu_personnalise ?>
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

<!--contenant du bandeau-->
<div id="brand-row">
<a href="<?php echo site_url().'/accueil'?>" class="custom-logo-link" rel="home" itemprop="url"><img max-width="349px" height="192px" src="<?php echo img_url($logo_entete) ?>" class="custom-logo" alt="Logo" itemprop="logo" srcset="<?php echo img_url($logo_entete) ?> 349w, <?php echo img_url($logo_entete) ?> 300w" sizes="(max-width: 349px) 100vw, 349px" /></a>
</div>
<!--contenant du bandeau-->

<!--contenu-->
<div class="palette ">
<div class="container ">
<div class="row">

<!--contenu de gauche-->
<div class="col-sm-9">

<!--articles-->
<?php
foreach ($articles as $article) {
	echo '<div class="blog-item blog-standard">';
	echo '<h2 class="blog-title">';
	if (!empty($liens)) echo $liens['lien_delete_debut'].$article->id.$liens['lien_delete_fin'];
	echo '<a href="'. site_url('accueil/article/'.$article->id).'" title="'.htmlspecialchars($article->titre).'">'.htmlspecialchars($article->titre).'</a>';
	echo '</h2>';
	echo '<div class="blog-meta ">';
	echo '</div>';
	echo '<div class="blog-img">';
	echo '<img width="833" height="500" src="'.img_url('placeholder.svg').'" data-src="'.base_url().'uploads/news/'.$article->nom_image.'" class="lazy attachment-underwood-pro-833-500 size-underwood-pro-833-500 wp-post-image" alt="'.htmlspecialchars($article->titre).'" /> </div>';
	echo '<div class="singular-entry">';
	echo '<p>'.nl2br(htmlspecialchars($article->description)).'</p>';
	echo '<p><i>Publié le '.date_format(date_create($article->date_creation),"d/m/Y").'</i></p>';
	echo '<div class="more_tag"><span><a href="'. site_url('accueil/article/'.$article->id).'">Lire l\'article</a></span></div>';
	echo '<span class="clearboth"></span>';
	echo '<div class="tags-wrap">';
	foreach ($articles_categories as $row) {
		if ($article->id == $row->id)
			echo '<a>'.$row->nom.'</a>';
	}
	echo '</div></div></div>';
}
?>
<br><br><br>
<!--articles-->

</div>
<!--contenu de gauche-->

<!--contenu de droite-->
<div class="col-sm-3 sidebar">

<!--news récentes-->
<div style="display:<?php echo empty($articles_recents) ? 'none' : 'block' ?>" id="recent-posts-2" class="widget widget_recent_entries widget sidebar_widget"><span class="widget_title">articles récents</span> <ul>
	<?php foreach ($articles_recents as $article) {
		echo '<li><a href="'.site_url().'/accueil/article/'.$article->id.'">'.htmlspecialchars($article->titre).'</a></li>';
	} ?>
</ul>
</div>
<!--news récentes-->

</div>
</div>
</div>
</div>

<!--footer-->
<footer id="footer-row" class="has-post-thumbnail" style="position: <?php echo empty($articles_recents) ? 'fixed' : 'absolute' ?>;bottom: 0;width: 100%;height: 90px;">

<div id="copyright-row">
<div class="container">
<div class="row">
<div class="col-sm-6">
<p class="copyright">© <?php echo date('Y') . ' ' . $commune ?></p>
</div>
<div class="col-md-6">
<p class="credit">Design by <a href="https://themeshift.com/">ThemeShift</a>.</p>
</div>
</div>
</div>
</div>
</footer>
<!--footer-->

<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/plugins/contact-form-7/includes/js/scripts.js?ver=4.8'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/jquery.easing.min.js?ver=1.3'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/nicescroll.min.js?ver=3.6.8'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/parallax.min.js?ver=1.4.2'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/fitvids.js?ver=1.1'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/jquery.bxslider.min.js?ver=4.1.2'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/slick.js?ver=1.6.0'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-content/themes/underwood-pro/assets/js/public.js?ver=1.0.0'></script>
<script type='text/javascript' src='https://demo2.themeshift.com/underwood/wp-includes/js/wp-embed.min.js?ver=4.8.8'></script>
</body>
<!--body-->
</html>