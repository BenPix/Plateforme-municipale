# Plateforme Municipale (Saas)
Cet outil a été imaginé pour répondre aux besoins des municipalités. Il sert d'outil de communication entre les services, de partage des tâches, et permet un suivi sur la réalisation de ces tâches.
Il a été implémenté via le framework CodeIgniter, et utilise les technologies du Web (HTML, CSS, Javascript), ainsi que PHP et MySQL. Il est totalement **Open Source**.
### Prérequis :
* Posséder un nom de domaine ou un sous-domaine.
  * Le serveur de votre hébergement doit posséder un dossier à la racine qui va héberger l’application. Ce dossier doit également posséder un dossier vide nommé www. Le (sous-)domaine **DOIT POINTER** vers ce dossier www.
* Posséder une base de données MySQL vide et ses identifiants d’accès
* Posséder le dossier de l’application (disponible dans ce repository).
* Pouvoir transférer l’application sur le serveur de l’hébergeur (via FTP ou SSH).
### Télécharger l’application sur le serveur de votre hébergement :
Les 3 dossiers *application_plateforme_municipale*, *system_plateforme_municipale* et *vendor* doivent être placés dans le dossier principal d'hébergement.
Les fichiers et dossiers contenus dans le dossier *www* doivent être placés dans le dossier *www* du dossier principal d'hébergement.
### Configurez l’application via le script d’installation :
1. Utilisez un navigateur et accédez à l’adresse de l’application (c’est-à-dire l’url du
(sous-)domaine) suivie de **/installation** (ex. https://ma-ville.pro/installation ou encore
https://plateforme.ma-ville.fr/installation)
2. La page affiche un formulaire à remplir avec les identifiants d’accès à la base de données. Munissez vous de ces informations, et remplissez le formulaire, puis cliquez sur Terminer l'installation.
   * Si tout s’est bien passé, vous serez invité à accéder à l’application via un bouton,
et vous pourrez continuer de paramétrer l’application selon vos besoins.
   * Si un problème est survenu pendant l’installation, il faudra configurer l’application
manuellement, avec l’aide d’un technicien informatique (vous pouvez également
vous référer à la documentation de CodeIgniter 3.0 pour effectuer cette
configuration manuellement)
### Paramétrez l’application selon vos besoins :
Cette partie doit être réalisée par le futur administrateur du site. Le compte admin sera créé, et
donnera accès à toute la gestion de l’application.
Il sera nécessaire de posséder un logo de la ville, au format jpg/jpeg/png (voire svg), de
maximum 100Ko, pour créer les logos de navigation et d’entête.
1. Remplissez la partie **Création du compte admin**. C’est avec ce compte que vous
administrerez votre application (gestion des utilisateurs, des demandes, etc).
2. Remplissez la partie **Paramétrage général**. Les images et nom de la ville seront utilisés
pour personnaliser l’application à votre image. Le choix des modules vous offre des
fonctionnalités supplémentaires. Il sera toujours possible de modifier ce choix
ultérieurement.
3. Cliquez sur *Finaliser*, et votre application est maintenant opérationnelle.
