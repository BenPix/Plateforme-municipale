<style>
	input {
		margin: 5px;
		padding: 5px;
	}
	.error {
		color:red;
	}
</style>

<h1>BIENVENUE !!</h1>
<p>Ce script va vous permettre de configurer l'application et terminer l'installation.</p>
<p>Veuillez remplir le formulaire ci-dessous pour configurer l'application et la connecter à la base de données.</p>
<strong>Identifiants de connexion à votre base de données.</strong>
<form method="post">
	<input name="hostname" placeholder="Nom d'hôte / hostname"/><br>
	<input name="username" placeholder="Nom d'utilisateur"/><br>
	<input name="password" placeholder="Mot de passe" type="password"/><br>
	<input name="database" placeholder="Nom de la base de données"/><br>
	<input type="submit" value="Terminer l'installation"/>
</form>
<br>