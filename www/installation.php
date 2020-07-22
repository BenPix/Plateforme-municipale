
<?php
/**
 * L'Application au service des Municipalités.
 *
 * Une application open source, conçue avec le framework PHP CodeIgniter.
 *
 * Le contenu de cette application peut être modifié comme bon vous semble.
 *
 * Cette notice d'utilisation doit être préservée dans chaque copie.
 *
 *
 * @author	Poux Benoit, pour la Mairie de Sains-en-Gohelle (62114) France
 * @link	https://sains-en-gohelle.pro
 * @since	Version 1.0.0
 */

 /*
 *---------------------------------------------------------------
 * VARIABLES DE CONFIGURATION
 *---------------------------------------------------------------
 *
 * Ces variables vont permettre de configurer le framework CodeIgniter
 * sans avoir à éditer les fichiers à la main.
 * Si par la suite vous désirez modifier la structure des dossiers,
 * changer l'adresse du site ou utiliser une autre base de données,
 * bref, effectuer des changements importants, vous serez invité à
 * configurer manuellement les fichiers adéquats dans CodeIgniter
 * en vous référant à la documentation du framework.
 */

$dossier_racine = substr(strtr(rtrim(getcwd(), '/\\'), '/\\', DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR), 0, -3);
$app_folder = $dossier_racine.'application_plateforme_municipale'.DIRECTORY_SEPARATOR;
$syst_folder = $dossier_racine.'system_plateforme_municipale'.DIRECTORY_SEPARATOR;
if (isset($_SERVER['HTTP_REFERER']))
	$page_actuelle = rtrim($_SERVER['HTTP_REFERER'], '/');
else {
	$protocol = (empty($_SERVER['HTTPS']) ? 'http' : 'https').'://';
	$page_actuelle = $protocol.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];
	$page_actuelle = rtrim($page_actuelle, '/');
}
// we subsitute the 'installation.php' from the full adresse : https://example.com/installation.php => https://example.com/
$site = substr($page_actuelle, 0, strpos($page_actuelle, 'installation'));

 /*
 *---------------------------------------------------------------
 * TEST DE CONNEXION A LA BDD VIA LE FICHIER
 *---------------------------------------------------------------
 *
 * On essaye de se connecter à la base de données avec les identifiants
 * qui se trouvent dans le fichier
 * /application_plateforme_municipale/config/database.php
 *
 * Si la connection est établie, on peut estimer que le script d'installation
 * a été lancé avec succès, et on peut désormais accéder au site.
 */

$database_file = $app_folder.'config'.DIRECTORY_SEPARATOR.'database.php';
$data = file($database_file);
$hostname = substr(trim($data[77]), 15, -2);
$username = substr(trim($data[78]), 15, -2);
$password = substr(trim($data[79]), 15, -2);
$database = substr(trim($data[80]), 15, -2);
$succed_connexion = TRUE;

try
{
    $sql = new PDO('mysql:host='.$hostname.';dbname='.$database, $username, $password);
}
catch (Exception $e)
{
	$succed_connexion = FALSE;
}

if ($succed_connexion) {
	?>

	<p>L'application a déjà été configurée. Cliquez sur le bouton ci-dessous pour y accéder.</p>
	<br>
	<a href="<?php echo $site ?>">
		<button>Accéder à l'application</button>
	</a>

	<?php
	die();
}

 /*
 *---------------------------------------------------------------
 * TEST DE CONNEXION A LA BDD VIA L'UTILISATEUR
 *---------------------------------------------------------------
 *
 * On essaye de se connecter à la base de données avec les données
 * envoyées en POST par l'utilisateur, via le formulaire.
 *
 * Il y a d'abord un test sur les données en POST, pour vérifier
 * que le formulaire a bien été soumis.
 *
 * Si la connection est établie, on peut éditer le fichier database.php
 * avec les identifiants envoyés par l'utilisateur.
 */

 $succed_connexion = TRUE; // reset de la variable

if ($formulaire_soumis = ( ! empty($_POST['hostname']) && ! empty($_POST['database']) )) {
	try
	{
	    $sql = new PDO('mysql:host='.$_POST['hostname'].';dbname='.$_POST['database'], $_POST['username'], $_POST['password']);
	}
	catch (Exception $e)
	{
	    // fomulaire soumis, mais données incorrectes
	    $succed_connexion = FALSE;
	}
} else {
	// le fomulaire n'a pas été soumis
	$succed_connexion = FALSE;
}

if ($succed_connexion) :
	$sql = NULL; // closing the connexion, because we still want to try the connexion after some changes

 /*
 *---------------------------------------------------------------
 * EDITION DU FICHIER DE CONNEXION A LA BASE DE DONNEES
 *---------------------------------------------------------------
 *
 * On édite le fichier de connexion à la base de données
 * /application_plateforme_municipale/config/database.php
 * avec les données envoyées en POST par l'utilisateur
 * via le formulaire.
 *
 * Les lignes modifiées sont celle contenant les informations :
 *
 * -Ligne 78-
 * hostname
 *
 * -Ligne 79-
 * username
 *
 * -Ligne 80-
 * password
 *
 * -Ligne 81-
 * database
 */

	if (strpos($data[77], "'hostname' => 'root'") !== FALSE)
		$data[77] = str_replace("root", $_POST['hostname'], $data[77]);

	if (strpos($data[78], "'username' => 'root'") !== FALSE)
		$data[78] = str_replace("root", $_POST['username'], $data[78]);

	if (strpos($data[79], "'password' => 'root'") !== FALSE)
		$data[79] = str_replace("root", $_POST['password'], $data[79]);

	if (strpos($data[80], "'database' => 'root'") !== FALSE)
		$data[80] = str_replace("root", $_POST['database'], $data[80]);

	file_put_contents($database_file, implode('', $data));

 /*
 *---------------------------------------------------------------
 * EDITION DU FICHIER DE CONNEXION A LA BASE DE DONNEES
 *---------------------------------------------------------------
 *
 * On édite le fichier /www/index.php avec les données correctes
 * qui on pu être déterminées avec le script.
 * 
 * Les lignes modifiées sont celle contenant les informations :
 *
 * -Ligne 100-
 * system_path
 *
 * -Ligne 117-
 * application_folder
 */

	$index_file = $dossier_racine.'www'.DIRECTORY_SEPARATOR.'index.php';

	$data = file($index_file);

	if (strpos($data[99], "system_path = 'system_plateforme_municipale';") !== FALSE)
		$data[99] = str_replace('system_plateforme_municipale', addslashes($syst_folder), $data[99]);

	if (strpos($data[116], "application_folder = 'application_plateforme_municipale';") !== FALSE)
		$data[116] = str_replace("application_plateforme_municipale", addslashes($app_folder), $data[116]);

	file_put_contents($index_file, implode('', $data));

 /*
 *---------------------------------------------------------------
 * EDITION DU FICHIER DE CONFIG
 *---------------------------------------------------------------
 *
 * On édite le fichier de configuration générale de l'application
 * /application_plateforme_municipale/config/config.php
 * 
 * Les lignes modifiées sont celle contenant les informations :
 *
 * -Ligne 26-
 * base_url
 *
 * -Ligne 139-
 * composer_autoload
 */

	$config = $app_folder.'config'.DIRECTORY_SEPARATOR.'config.php';
	$vendor_folder = $dossier_racine.'vendor'.DIRECTORY_SEPARATOR;

	$data = file($config);

	if (strpos($data[25], "config['base_url'] = '';") !== FALSE)
		$data[25] = str_replace("''", "'".$site."'", $data[25]);

	if (strpos($data[138], "config['composer_autoload'] = 'vendor/autoload.php';") !== FALSE)
		$data[138] = str_replace("vendor/", addslashes($vendor_folder), $data[138]);

	file_put_contents($config, implode('', $data));

 /*
 *---------------------------------------------------------------
 * TEST DE REUSSITE SUITE A L'EDITION DES FICHIERS
 *---------------------------------------------------------------
 *
 * On vérifie que les 3 fichiers ont été édité correctement :
 * /application_plateforme_municipale/config/config.php
 * /application_plateforme_municipale/config/database.php
 * /www/index.php
 */

	 $error_message = '';
	 $erreur = FALSE;

	// d'abord le system path
	if (($_temp = realpath($syst_folder)) !== FALSE)
	{
		$syst_folder = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$syst_folder = strtr(
			rtrim($syst_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($syst_folder))
	{
		$error_message .= '<p class="error">Le chemin de votre dossier system semble ne pas être configuré correctement. Veuillez ouvrir le fichier index.php et corriger ceci: '.pathinfo(__FILE__, PATHINFO_BASENAME).'</p>';
		$error_message .= '<br>';
		$erreur = TRUE;
	}

	// ensuite l'application path
	if (is_dir($app_folder))
	{
		if (($_temp = realpath($app_folder)) !== FALSE)
		{
			$app_folder = $_temp;
		}
		else
		{
			$app_folder = strtr(
				rtrim($app_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$app_folder.DIRECTORY_SEPARATOR))
	{
		$app_folder = BASEPATH.strtr(
			trim($app_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		$error_message .= '<p class="error">Le chemin de votre dossier application semble ne pas être configuré correctement. Veuillez ouvrir le fichier index.php et corriger ceci: '.SELF.'</p>';
		$error_message .= '<br>';
		$erreur = TRUE;
	}

	// et enfin la connexion à la base de données
	$data = file($database_file);
	$hostname = substr(trim($data[77]), 15, -2);
	$username = substr(trim($data[78]), 15, -2);
	$password = substr(trim($data[79]), 15, -2);
	$database = substr(trim($data[80]), 15, -2);

	try
	{
	    $sql = new PDO('mysql:host='.$hostname.';dbname='.$database, $username, $password);
	}
	catch (Exception $e)
	{
		$error_message .=  '<p class="error">La configuration de votre base de données semble avoir posé problème. Veuillez ouvrir le fichier database.php dans le dossier config de l\'application, et corriger les identifiants de connexion.</p>';
		$error_message .= '<br>';
		$erreur = TRUE;
	}

	if ($erreur) {
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo $error_message;
		exit(3);
	}
	?>

<!-- warning and redirecting section -->
<p>L'application a été configurée avec succès. Cliquez sur le bouton ci-dessous pour y accéder.</p>
<br>
<a href="<?php echo $site ?>">
<button>Accéder à l'application</button>
</a>
<!-- end of the warning and redirecting section -->

	<?php
	die();

endif;

 /*
 *---------------------------------------------------------------
 * AFFICHAGE DU FORMULAIRE DES IDENTIFIANTS BDD
 *---------------------------------------------------------------
 *
 * On affiche le formulaire des identifiants de connexion à la base
 * de données.
 *
 * On affiche aussi un message dans le cas où le formulaire a été
 * soumis avec des identifiants erronés.
 */

$warning = $formulaire_soumis ? '<p class="error">Erreur : Connexion à la base de données échouée. Les identifiants de connexion sont erronés, veuillez recommencer.</p>' : '' ;

include('info.php'); // formulaire des identifiants à la base de données

die($warning);
?>
 