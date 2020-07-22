<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Mail_model extends CI_Model
{

	static $_commune = 'votre ville';
	protected $table = 'mail_admin';

	public function __construct() {
		parent::__construct();

		$commune = $this->settings_query_model->empty_database() ? 'nom de commune inconnu' : htmlspecialchars(ucwords($this->connexion_model->check_nom_commune(), ' -'), ENT_NOQUOTES, 'UTF-8', FALSE);

		self::$_commune = $commune;
	}

	public function envoi_demande_validation($demande)
	{
		// contenu
		$destinataire = $demande->mail_responsable_attache;
		$champs = array('demande_interservices_id' => $demande->id);
		$pieces_jointes = $this->upload_query_model->read_for_email($champs);
		$message = $this->message_demande_validation($demande);
		$objet = 'Demande interservice à valider';
		// boundary
		$boundary = md5(uniqid(rand(), true));
		// headers
		$cc = array();
		$headers = $this->structureMailHeaders($boundary, $cc);
		// body
		$body = $this->structureMailBody($boundary, $message, $pieces_jointes);
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_demande_inscription($utilisateur, $email, $pole)
	{
		// contenu
		$destinataire = $this->mail_admin();
		$body = $this->message_demande_inscription($utilisateur, $email, $pole);
		$objet = 'Inscription à valider : '.htmlspecialchars($utilisateur->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($utilisateur->prenom, ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_inscription_en_attente($utilisateur, $email, $pole)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_inscription_en_attente($utilisateur, $email, $pole);
		$objet = 'Inscription enregistrée : '.htmlspecialchars($utilisateur->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($utilisateur->prenom, ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_inscription_validee($user)
	{
		// contenu
		$destinataire = $user->email;
		$body = $this->message_inscription_validee($user);
		$objet = 'Inscription validée';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_inscription_refusee($email)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_inscription_refusee();
		$objet = 'Inscription refusée';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_demande_refusee($demande, $refus)
	{
		// contenu
		$destinataire = $demande->mail_demandeur;
		$body = $this->message_demande_refusee($demande, $refus);
		$objet = ($demande->num_dossier != 0) ? 'Intervention N°'.$demande->num_dossier.' - REFUSÉE' : 'Demande d\'intervention - REFUSÉE';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		$headers .= 'CC: '.$demande->mail_responsable_attache."\n";
		// $headers .= 'CC: '.$demande->mail_sollicite."\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_new_password($email, $token, $utilisateur_id)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_new_password($token, $utilisateur_id);
		$objet = 'Réinitialisation du mot de passe';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_news_a_valider($email, $article)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_news_a_valider($article);
		$objet = 'Article N°'.$article->id.' en attente de validation';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_delegation_activee_delegue($email, $responsable, $polesDelegues)
	{
		// contenu
		$destinataire = $email;
		$body = $this->_delegation_activee_delegue($responsable, $polesDelegues);
		$objet = htmlspecialchars($responsable, ENT_NOQUOTES, 'UTF-8', FALSE).' vous a délégué son rôle de responsable';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_delegation_activee_responsable($email, $delegue, $polesDelegues)
	{
		// contenu
		$destinataire = $email;
		$body = $this->_delegation_activee_responsable($delegue, $polesDelegues);
		$objet = 'Vous avez délégué votre rôle de responsable à '.htmlspecialchars($delegue->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($delegue->prenom, ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_delegation_desactivee_delegue($email, $responsable, $polesDelegues)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_delegation_desactivee_delegue($responsable, $polesDelegues);
		$objet = htmlspecialchars($responsable, ENT_NOQUOTES, 'UTF-8', FALSE).' a repris son rôle de responsable';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_delegation_desactivee_responsable($email, $delegue, $polesDelegues)
	{
		// contenu
		$destinataire = $email;
		$body = $this->message_delegation_desactivee_responsable($delegue, $polesDelegues);
		$objet = 'Vous avez mis fin à la délégation de '.htmlspecialchars($delegue->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($delegue->prenom, ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_note_a_valider($note)
	{
		// destinataires
		$destinataire = $note->valideur_attendu_email;
		$body = $this->message_note_a_valider($note);
		$objet = 'Note N°'.$note->id.' : '.$note->objet.' - A VALIDER OU REFUSER';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_note_new_comment($note)
	{
		// destinataires
		$destinataires = array_column($note->workflow, 'workflow_utilisateur_email');
		$destinataires[] = $note->redacteur_email;
		unset($destinataires[array_search($this->session->userdata('email'), $destinataires)]); // removing the active user
		$destinataire = reset($destinataires);
		// contenu
		$last_comment = end($note->commentaires);
		$body = $this->message_note_new_comment($note, $last_comment);
		$objet = 'Note N°'.$note->id.' : '.$note->objet.' - Nouveau commentaire de '.$last_comment->utilisateur;
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($destinataires as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_note_terminee($note)
	{
		// destinataires
		$destinataires = array_column($note->workflow, 'workflow_utilisateur_email');
		$destinataires[] = $note->redacteur_email;
		unset($destinataires[array_search($this->session->userdata('email'), $destinataires)]); // removing the active user
		$destinataire = reset($destinataires);
		// contenu
		$body = $this->message_note_terminee($note);
		$objet = 'Note N°'.$note->id.' : '.$note->objet.' - TERMINÉE';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($destinataires as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoi_note_refusee($note)
	{
		// destinataires
		$destinataires = array_column($note->workflow, 'workflow_utilisateur_email');
		$destinataires[] = $note->redacteur_email;
		unset($destinataires[array_search($this->session->userdata('email'), $destinataires)]); // removing the active user
		$destinataire = reset($destinataires);
		// contenu
		$last_comment = end($note->commentaires);
		$body = $this->message_note_refusee($note, $last_comment);
		$objet = 'Note N°'.$note->id.' : '.$note->objet.' - Refusée par '.$last_comment->utilisateur;
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($destinataires as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierRefusBDC($bdc, $email)
	{
		// contenu
		$destinataire = $email;
		$body = $this->messageRefusDemandeBDC($bdc);
		$objet = 'Demande de bon de commande N°'.$bdc->id.' - REFUSÉE';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeBDCAcceptee($bdc, $destinataires, $commentaires)
	{
		// destinataire
		$destinataire = $destinataires[0];
		// commentaires
		$comments = empty($commentaires) ? '' : '<strong><u>Commentaires : </u></strong><br>';

		foreach ($commentaires as $row)
			$comments .= '<strong>'.$row->commentateur.'</strong> le '.$row->horodateur.'<br><i>'.nl2br(htmlspecialchars($row->commentaire, ENT_NOQUOTES, 'UTF-8', FALSE)).'</i><br><br>';
		// contenu
		$body = $this->messageDemandeBDCAcceptee($bdc, $comments);
		$objet = 'Demande de bon de commande N°'.$bdc->id.' - ACCEPTÉE';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($destinataires as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeBDCAValider($bdc, $destinataires)
	{
		// contenu
		$destinataire = $destinataires[0];
		$body = $this->messageDemandeBDC($bdc);
		$objet = 'Demande de bon de commande N°'.$bdc->id.' - A VALIDER OU REFUSER';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($destinataires as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierValideBDC($bdc, $email)
	{
		// contenu
		$destinataire = $email;
		$body = $this->messageDemandeBDCValide($bdc);
		$objet = 'Demande de bon de commande N°'.$bdc->id.' - VALIDÉE';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";
		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierBDCARevalider($bdc)
	{
		// contenu
		$destinataire = $bdc->responsable_email;
		$body = $this->messageDemandeBDCARevalider($bdc);
		$objet = 'Demande de bon de commande N°'.$bdc->id.' - VALIDATION A RÉITÉRER';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenTransfert($demande)
	{
		// contenu
		$destinataire = $demande->email;
		$body = $this->messageDemandeCitoyenTransfert($demande);
		$objet = self::$_commune . ' : Traitement de votre demande N°'.$demande->id.' - Transférée au service : '.htmlspecialchars($demande->transfert, ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenSiteTransfert($demande, $exceptions)
	{
		// contenu
		$destinataire = $demande['email'];
		$body = $this->messageDemandeCitoyenSiteTransfert($demande, $exceptions);
		$objet = self::$_commune . ' : Traitement de votre demande N°'.$demande['id'].' - Transférée au service : '.htmlspecialchars($demande['transfert'], ENT_NOQUOTES, 'UTF-8', FALSE);
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenReponse($demande, $pieces_jointes)
	{
		// contenu
		$destinataire = $demande->email;
		$message = $this->messageDemandeCitoyenReponse($demande);
		$objet = self::$_commune . ' : Traitement de votre demande N°'.$demande->id.' - Réponse';
		// boundary
		$boundary = md5(uniqid(rand(), true));
		// headers
		$cc = array();
		$headers = $this->structureMailHeaders($boundary, $cc);
		// body
		$body = $this->structureMailBody($boundary, $message, $pieces_jointes);

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}



	public function envoiCourrierDemandeCitoyenReponseMairie($demande, $email)
	{
		// contenu
		$destinataire = $email;
		$body = $this->messageDemandeCitoyenReponseMairie($demande);
		$objet = self::$_commune . ' : Demande N°'.$demande->id. ' - Traitée';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenSiteReponse($demande, $exceptions, $pieces_jointes)
	{
		// contenu
		$destinataire = $demande['email'];
		$message = $this->messageDemandeCitoyenSiteReponse($demande, $exceptions);
		$objet = self::$_commune . ' : Traitement de votre demande N°'.$demande['id'].' - Réponse';
		// boundary
		$boundary = md5(uniqid(rand(), true));
		// headers
		$cc = array();
		$headers = $this->structureMailHeaders($boundary, $cc);
		// body
		$body = $this->structureMailBody($boundary, $message, $pieces_jointes);

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenSiteReponseMairie($demande, $exceptions, $email)
	{
		// contenu
		$destinataire = $email;
		$body = $this->messageDemandeCitoyenSiteReponseMairie($demande, $exceptions);
		$objet = self::$_commune . ' : Demande N°'.$demande['id']. ' - Traitée';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenATraiter($demande, $email_responsable, $email_agents_sous_pole = array())
	{
		// contenu
		$destinataire = $email_responsable;
		$body = $this->messageDemandeCitoyenATraiter($demande);
		$objet = self::$_commune . ' : Demande citoyen N°'.$demande->id.' - A traiter';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($email_agents_sous_pole as $email)
			$headers .= 'CC: '.$email."\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}

	public function envoiCourrierDemandeCitoyenSiteATraiter($demande, $email_responsable, $exceptions, $email_agents_sous_pole = array())
	{
		// contenu
		$destinataire = $email_responsable;
		$body = $this->messageDemandeCitoyenSiteATraiter($demande, $exceptions);
		$objet = self::$_commune . ' : Demande citoyen N°'.$demande['id'].' - A traiter';
		// headers
		$headers = "MIME-Version: 1.0" . "\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\n";
		$headers .= 'From: <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		foreach ($email_agents_sous_pole as $email)
			$headers .= 'CC: '.$email."\n";

		// envoi du mail
		$param = array('destinataire' => $destinataire, 'objet' => $objet, 'body' => $body, 'headers' => $headers);
		$this->mailer_library->do_in_background(mail_url(), $param);
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	* METHODES PRIVEES
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/

	private function mail_admin()
	{
		$this->db->select('mail');
		$this->db->from($this->table);
		$this->db->where('id=1');
		$query = $this->db->get();
		$result = $query->result_array();

		return $result[0]['mail'];
	}

	private function structureMailBody($boundary, $message, $pieces_jointes) {
		$body = 'This is a multi-part message in MIME format.'."\n";
		$body .= '--'.$boundary."\n";

		// autres en tetes
		$body .= 'Content-Type: text/html; charset="UTF-8"'."\n";
		$body .= "\n";

		//1ere partie, avec le message
		$body .= $message;

		// fin du message, donc saut a la ligne
		$body .= "\n";

		//composition des autres parties selon le nombre de pièces jointes
		for ($i = 0; $i < count($pieces_jointes); $i++) {
			$body .= '--'.$boundary."\n";

		// affichage du type de fichier dans le content type + le nom
			$body .= 'Content-Type: '.$pieces_jointes[$i]->file_type.'; name="'.$pieces_jointes[$i]->file_name.'"'."\n";
			$body .= 'Content-Transfer-Encoding: base64'."\n";
			$body .= 'Content-Disposition: attachment; filename="'.$pieces_jointes[$i]->file_name.'"'."\n";
			$body .= "\n";

		// recupération du fichier et encodage selon les normes
			$source = file_get_contents($pieces_jointes[$i]->full_path);
			$source = base64_encode($source);
			$source = chunk_split($source);
			$body .= $source;
		}
		
		// fin du mail
		$body .= "\n".'--'.$boundary.'--';

		return $body;
	}

	private function structureMailHeaders($boundary, $cc) {
		$headers = 'Content-Type: multipart/mixed;'."\n".' boundary="'.$boundary.'"'."\n";
		$headers .= "MIME-Version: 1.0"."\n";

		foreach ($cc as $email) {
			$headers .= 'CC: '.$email."\n";
		}
		$headers .= 'From: '.str_replace(' ', '-', self::$_commune).' <robot@' . $_SERVER['HTTP_HOST'] . '>' . "\n";

		return $headers;
	}

	private function message_demande_validation($demande) {
		$date_souhaitee = ($demande->date_souhaitee == NULL) ? 'indéterminé' : $demande->date_souhaitee;
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Un demande interservice est en attente de validation de votre part.<br><br>

		Extrait de la demande de : <u>'.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'</u><br>
		pour la direction suivante : '.htmlspecialchars($demande->direction_sollicitee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Horodateur : </strong>'.$demande->horodateur.'<br>
		<strong>Direction attachée : </strong>'.htmlspecialchars($demande->direction_attachee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		<strong>Direction sollicitée : </strong>'.htmlspecialchars($demande->direction_sollicitee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Demande : </strong><br>'.nl2br(htmlspecialchars($demande->demande, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		<strong>Date souhaitée pour la réalisation : </strong>'.$date_souhaitee.'<br>
		<strong>Délai : </strong>'.$demande->delai.'<br>
		<strong>Degré d\'urgence pour les interventions techniques : </strong>'.$demande->degre_urgence.'<br><br>

		Merci de bien vouloir valider ou refuser cette demande sur le <a href="'.site_url('validation/inscriptions').'">site</a>.<br>
		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_demande_inscription($user, $email, $pole) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Une inscription vient d\'être réalisée, elle est en attente de validation de votre part.<br><br>

		<strong>Nom : </strong>'.htmlspecialchars($user->nom).'<br>
		<strong>Prénom : </strong>'.htmlspecialchars($user->prenom).'<br>
		<strong>Email : </strong>'.htmlspecialchars($email).'<br>
		<strong>Appartenance : </strong>'.htmlspecialchars($pole, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<a href="'.site_url('validation/interservices').'">Lien vers le site</a><br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_inscription_en_attente($user, $email, $pole) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Votre inscription a bien été enregistrée, elle est en attente de validation de la part de la Direction.<br><br>

		Vous serez informé par email lorsque votre inscription aura été validée.<br><br>

		<strong>Nom : </strong>'.htmlspecialchars($user->nom).'<br>
		<strong>Prénom : </strong>'.htmlspecialchars($user->prenom).'<br>
		<strong>Pseudonyme : </strong>'.htmlspecialchars($user->pseudo).'<br>
		<strong>Email : </strong>'.htmlspecialchars($email).'<br>
		<strong>Appartenance : </strong>'.htmlspecialchars($pole, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_inscription_validee($user) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Votre inscription vient d\'être validée, vous pouvez désormais vous connecter sur le site <a href="'.base_url().'">'.base_url().'</a>.<br><br>

		<strong>Renseignements sur le compte :</strong><br>
		<u>Nom :</u> '.htmlspecialchars($user->nom).'<br>
		<u>Prénom :</u> '.htmlspecialchars($user->prenom).'<br>
		<u>Service :</u> '.htmlspecialchars($user->pole, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		<u>Pseudonyme :</u> '.htmlspecialchars($user->pseudo).'<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_inscription_refusee() {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Votre inscription à la plateforme municipale a été refusée.<br><br>

		En cas de soucis, contactez la Direction.<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_demande_refusee($demande, $refus) {
		$texteDossier1 = 'Votre demande d\'intervention';
		$texteDossier2 = '';

		if ($demande->num_dossier != 0) {
			$texteDossier1 = 'La demande d\'intervention N°'.$demande->num_dossier;
			$texteDossier2 = '<strong>Dossier N° </strong>'.$demande->num_dossier.'<br>';
		}
		
		$date_souhaitee = ($demande->date_souhaitee == NULL) ? 'indéterminé' : $demande->date_souhaitee;
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		'.$texteDossier1.' a été refusée par '.htmlspecialchars($demande->refuseur, ENT_NOQUOTES, 'UTF-8', FALSE).'.<br><br>

		La raison du refus est la suivante :<br>
		'.nl2br(htmlspecialchars($refus, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		<u>Récapitulatif de la demande :</u><br><br>

		Demande de : '.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		pour la direction suivante : '.htmlspecialchars($demande->direction_sollicitee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Horodateur : </strong>'.$demande->horodateur.'<br>
		<strong>Direction attachée : </strong>'.htmlspecialchars($demande->direction_attachee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		<strong>Direction sollicitée : </strong>'.htmlspecialchars($demande->direction_sollicitee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Demande : </strong><br>'.nl2br(htmlspecialchars($demande->demande, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		<strong>Date souhaitée pour la réalisation : </strong>'.$date_souhaitee.'<br>
		<strong>Délai : </strong>'.$demande->delai.'<br>
		<strong>Degré d\'urgence pour les interventions techniques : </strong>'.$demande->degre_urgence.'<br>'
		.$texteDossier2.'<br>

		Bien cordialement<br><br>

		'.htmlspecialchars($demande->direction_sollicitee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_new_password($token, $utilisateur_id) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Il semblerait que vous avez demandé à réinitialiser votre mot de passe.<br>
		Si vous n\'êtes pas à la base de cette opération, supprimez cet email.<br><br>

		Pour saisir votre nouveau mot de passe, veuillez cliquer sur le lien ci-dessous.<br>
		<a href="'.base_url().'index.php/password/new_password/'.$token.'/'.$utilisateur_id.'">Plateforme municipale</a><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_news_a_valider($article) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Un nouvel article vient d\'être rédigé, il est en attente de validation de votre part.<br><br>

		<strong>Rédigé par : </strong>'.htmlspecialchars($article->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		<strong>Titre : </strong>'.htmlspecialchars($article->titre, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		<strong>Description : </strong>'.htmlspecialchars($article->description, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<a href="'.base_url().'">Lien vers le site</a><br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_delegation_activee_delegue($responsable, $polesDelegues) {
		if (count($polesDelegues) === 1)
			$phrase = 'Le pôle suivant est désormais à votre charge : ';
		else
			$phrase = 'Les pôles suivants sont désormais à votre charge : ';

		foreach ($polesDelegues as $row) {
			$phrase .= htmlspecialchars($row['nom'], ENT_NOQUOTES, 'UTF-8', FALSE).',';
		}
		$phrase = rtrim($phrase, ',');

		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		'.htmlspecialchars($responsable, ENT_NOQUOTES, 'UTF-8', FALSE).' vous a délégué son rôle de responsable de manière temporaire.<br><br>

		'.$phrase.'<br><br>

		<a href="'.base_url().'">Lien vers le site</a><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_delegation_activee_responsable($delegue, $polesDelegues) {
		if (count($polesDelegues) === 1)
			$phrase = 'Le pôle suivant est désormais à sa charge : ';
		else
			$phrase = 'Les pôles suivants sont désormais à sa charge : ';

		foreach ($polesDelegues as $row) {
			$phrase .= htmlspecialchars($row['nom'], ENT_NOQUOTES, 'UTF-8', FALSE).', ';
		}
		$phrase = rtrim($phrase, ', ');
		
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Vous avez délégué votre rôle de responsable de manière temporaire à '.htmlspecialchars($delegue->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($delegue->prenom, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		'.$phrase.'<br><br>

		<a href="'.base_url().'">Lien vers le site</a><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_delegation_desactivee_delegue($responsable, $polesDelegues) {
		if (count($polesDelegues) === 1)
			$phrase = 'Le pôle suivant n\'est désormais plus à votre charge : ';
		else
			$phrase = 'Les pôles suivants ne sont désormais plus à votre charge : ';

		foreach ($polesDelegues as $row) {
			$phrase .= htmlspecialchars($row['nom'], ENT_NOQUOTES, 'UTF-8', FALSE).',';
		}
		$phrase = rtrim($phrase, ',');

		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		'.htmlspecialchars($responsable, ENT_NOQUOTES, 'UTF-8', FALSE).' a repris son rôle de responsable.<br><br>

		'.$phrase.'<br><br>

		<a href="'.base_url().'">Lien vers le site</a><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_delegation_desactivee_responsable($delegue, $polesDelegues) {
		if (count($polesDelegues) === 1)
			$phrase = 'Le pôle suivant est de nouveau à votre charge : ';
		else
			$phrase = 'Les pôles suivants sont de nouveau à votre charge : ';

		foreach ($polesDelegues as $row) {
			$phrase .= htmlspecialchars($row['nom'], ENT_NOQUOTES, 'UTF-8', FALSE).', ';
		}
		$phrase = rtrim($phrase, ', ');
		
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Vous avez repris votre rôle de responsable, et mis fin à la délégation de '.htmlspecialchars($delegue->nom, ENT_NOQUOTES, 'UTF-8', FALSE).' '.htmlspecialchars($delegue->prenom, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		'.$phrase.'<br><br>

		<a href="'.base_url().'">Lien vers le site</a><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>

		

		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';
		
		return $message;
	}

	private function message_note_a_valider($note) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Voici une note en attente de validation de votre part. Elle a été rédigée par '.htmlspecialchars($note->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<u>Contenu de la note : </u><br>
		<strong>'.nl2br(htmlspecialchars($note->note, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br><br>

		Merci de bien vouloir valider ou refuser cette note sur le <a href="'.site_url('note/detail/'.$note->id).'">site</a>. Vous la trouverez dans la section <strong>NOTES A VALIDER</strong><br><br>

		Vous pouvez également y laisser un commentaire, et suivre son avancement.<br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_note_new_comment($note, $last_comment) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Un commentaire vient d\'être posté par '.htmlspecialchars($last_comment->utilisateur, ENT_NOQUOTES, 'UTF-8', FALSE).' sur la note N°'.$note->id.'.<br><br>

		<u>Le commentaire : </u><br>
		<strong>'.nl2br(htmlspecialchars($last_comment->commentaire, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br><br>

		<u>Rappel des détails de la note : </u><br>
		<strong>'.nl2br(htmlspecialchars($note->note, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br>
		Rédigée par '.htmlspecialchars($note->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		Vous pouvez consulter cette note sur le <a href="'.site_url('note/detail/'.$note->id).'">site</a>.<br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_note_terminee($note) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		La note N°'.$note->id.' vient d\'être validée par le dernier utilisateur dans la chaîne de validation. Elle est donc terminée. Elle était rédigée par : '.htmlspecialchars($note->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<u>Rappel des détails de la note : </u><br>
		<strong>'.nl2br(htmlspecialchars($note->note, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br>
		Rédigée par '.htmlspecialchars($note->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		Vous pouvez consulter cette note sur le <a href="'.site_url('note/detail/'.$note->id).'">site</a>. Vous la trouverez dans la section <strong>NOTES TERMINÉES</strong><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function message_note_refusee($note, $last_comment) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		La note N°'.$note->id.' vient d\'être refusée par '.htmlspecialchars($last_comment->utilisateur, ENT_NOQUOTES, 'UTF-8', FALSE).'.<br><br>

		<u>La raison du refus : </u><br>
		<strong>'.nl2br(htmlspecialchars($last_comment->commentaire, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br><br>

		<u>Rappel des détails de la note : </u><br>
		<strong>'.nl2br(htmlspecialchars($note->note, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br>
		Rédigée par '.htmlspecialchars($note->redacteur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		Vous pouvez consulter cette note sur le <a href="'.site_url('note/detail/'.$note->id).'">site</a>. Vous la trouverez dans la section <strong>NOTES REFUSÉES</strong><br><br>

		Bien cordialement<br><br>

		La Direction Générale<br><br><br><br>



		-- Ne répondez pas à cet email, il a été envoyé de manière automatique.
		</body>
		</html>';

		return $message;
	}

	private function messageRefusDemandeBDC($demande) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Vous avez refusé la demande de bon de commande de : '.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		pour la direction suivante : '.htmlspecialchars($demande->direction_concernee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Expression de la demande : </strong><br>'.nl2br(htmlspecialchars($demande->expression, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		La raison de votre refus sera communiquée aux personnes concernées.<br>
		<strong>Raison du refus : </strong><br>'.nl2br(htmlspecialchars($demande->raison_refus, ENT_NOQUOTES, 'UTF-8', FALSE)).'.<br><br>

		Bien cordialement<br><br>

		La Direction Générale
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeBDCAcceptee($demande, $comments) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		La demande de bon de commande N°'.$demande->id.' a été acceptée.<br><br><br>
		

		<u>Récapitulatif de la demande :</u><br><br>

		<strong>Demande réalisée par : </strong><br>'.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>
		<strong>Direction concernée : </strong><br>'.htmlspecialchars($demande->direction_concernee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>
		<strong>Expression de la demande : </strong><br>'.nl2br(htmlspecialchars($demande->expression, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		'.$comments.'


		<br>
		Bien cordialement<br><br>

		La Direction Générale
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeBDCValide($demande) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Vous avez validé la demande de bon de commande N°'.$demande->id.' de : '.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		pour la direction suivante : '.htmlspecialchars($demande->direction_concernee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Expression de la demande : </strong><br>'.nl2br(htmlspecialchars($demande->expression, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		Bien cordialement<br><br>

		La Direction Générale
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeBDCARevalider($demande) {
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		La demande de bon de commande N°'.$bdc->id.' est en attente de validation de la part de <strong>'.htmlspecialchars($bdc->elu_qui_valide, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong> depuis plus de 2 jours. Il vous est donc demandé de recommencer la procédure de validation, et de choisir à nouveau un élu pour valider ensuite.<br><br>

		<u>Récapitulatif de la demande :</u><br>
		Voici une demande de bon de commande de : '.htmlspecialchars($demande->demandeur, ENT_NOQUOTES, 'UTF-8', FALSE).'<br>
		pour la direction suivante : '.htmlspecialchars($demande->direction_concernee, ENT_NOQUOTES, 'UTF-8', FALSE).'<br><br>

		<strong>Expression de la demande : </strong><br>'.nl2br(htmlspecialchars($demande->expression, ENT_NOQUOTES, 'UTF-8', FALSE)).'<br><br>

		Merci de bien vouloir valider ou refuser cette demande sur le <a href="'.base_url().'">site</a>.<br><br>

		Si cette demande n\'apparaît pas sur le site, c\'est qu\'elle a été validée ou refusée par la Direction.<br>
		Bien cordialement<br><br>

		La Direction Générale
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenTransfert($demande)
	{
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Votre demande a été tranférée : <strong>'.htmlspecialchars($demande->transfert, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong>.<br>
		Elle sera traitée très prochainement.<br><br>

		<u>Récapitulatif de votre demande :</u><br>
		<strong>Numéro</strong> : '.$demande->id.'<br>
		<strong>Date d\'envoi</strong> : '.$demande->horodateur.'<br>'.
		$demande->form_value_display_for_mail.'<br>

		Nous vous remercions d\'avoir pris contact avec nous.<br>
		Cordialement<br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenSiteTransfert($demande, $exceptions)
	{
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Votre demande a été tranférée : <strong>'.htmlspecialchars($demande['transfert'], ENT_NOQUOTES, 'UTF-8', FALSE).'</strong>.<br>
		Elle sera traitée très prochainement.<br><br>

		<u>Récapitulatif de votre demande :</u><br>
		Numéro : <strong>'.$demande['id'].'</strong><br>
		Date d\'envoi : <strong>'.$demande['horodateur'].'</strong><br><br>';

		foreach ($demande as $key => $value) {
			if ( ! in_array($key, $exceptions) )
				$message .= $key.' : <strong>'.htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>';
		}
		$message .= '<br>

		Nous vous remercions d\'avoir pris contact avec nous.<br>
		Cordialement<br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenReponse($demande)
	{
		$transfert = empty($demande->transfert) ? 'Pôle Service à la Population' : $demande->transfert;
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Le service <strong>'.htmlspecialchars($transfert, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong> a répondu à votre demande :<br><br>

		<u>Réponse :</u><br>
		<i>'.nl2br(htmlspecialchars($demande->reponse, ENT_NOQUOTES, 'UTF-8', FALSE)).'</i><br><br>

		<u>Récapitulatif de votre demande :</u><br>
		<strong>Numéro</strong> : '.$demande->id.'<br>
		<strong>Date d\'envoi</strong> : '.$demande->horodateur.'<br>'.
		$demande->form_value_display_for_mail.'<br>

		Nous vous remercions d\'avoir pris contact avec nous.<br>
		Cordialement<br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenReponseMairie($demande)
	{
		$localisation = empty($demande->localisation) ? 'indéterminée' : $demande->localisation;
		$transfert = empty($demande->transfert) ? 'Pôle Service à la Population' : $demande->transfert;
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Un agent du service <strong>'.htmlspecialchars($transfert, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong> a répondu à la demande N°'.$demande->id.' provenant de l\'application :<br><br>

		<u>Réponse :</u><br>
		<i>'.nl2br(htmlspecialchars($demande->reponse, ENT_NOQUOTES, 'UTF-8', FALSE)).'</i><br><br>

		<u>Récapitulatif de la demande :</u><br>
		Numéro : <strong>'.$demande->id.'</strong><br>
		Date d\'envoi : <strong>'.$demande->horodateur.'</strong><br>
		Localisation : <strong>'.htmlspecialchars($localisation, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>
		Objet : <strong>'.htmlspecialchars($demande->objet, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>
		Message : <strong><br>'.nl2br(htmlspecialchars($demande->message, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenSiteReponse($demande, $exceptions)
	{
		$transfert = empty($demande['transfert']) ? 'Pôle Service à la Population' : htmlspecialchars($demande['transfert'], ENT_NOQUOTES, 'UTF-8', FALSE);
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Le service <strong>'.htmlspecialchars($transfert, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong> a répondu à votre demande :<br><br>

		<u>Réponse :</u><br>
		<i>'.nl2br(htmlspecialchars($demande['reponse'], ENT_NOQUOTES, 'UTF-8', FALSE)).'</i><br><br>

		<u>Récapitulatif de votre demande :</u><br>
		Numéro : <strong>'.$demande['id'].'</strong><br>
		Date d\'envoi : <strong>'.$demande['horodateur'].'</strong><br><br>';

		foreach ($demande as $key => $value) {
			if ( ! in_array($key, $exceptions) )
				$message .= $key.' : <strong>'.htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>';
		}
		$message .= '<br>

		Nous vous remercions d\'avoir pris contact avec nous.<br>
		Cordialement<br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenSiteReponseMairie($demande, $exceptions)
	{
		$transfert = empty($demande['transfert']) ? 'Pôle Service à la Population' : htmlspecialchars($demande['transfert'], ENT_NOQUOTES, 'UTF-8', FALSE);
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Un agent du service <strong>'.$transfert.'</strong> a répondu à la demande citoyen N°'.$demande['id'].' provenant du site :<br><br>

		<u>Réponse :</u><br>
		<i>'.nl2br(htmlspecialchars($demande['reponse'], ENT_NOQUOTES, 'UTF-8', FALSE)).'</i><br><br>

		<u>Récapitulatif de votre demande :</u><br>
		Numéro : <strong>'.$demande['id'].'</strong><br>
		Date d\'envoi : <strong>'.$demande['horodateur'].'</strong><br><br>';

		foreach ($demande as $key => $value) {
			if ( ! in_array($key, $exceptions) )
				$message .= $key.' : <strong>'.htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>';
		}
		$message .= '<br><br>

		La Mairie de '.self::$_commune.'
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenATraiter($demande)
	{
		$localisation = empty($demande->localisation) ? 'indéterminée' : $demande->localisation;
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Une demande citoyen a été tranférée à votre Service.<br>
		Elle devra être traitée dans les 2 mois, sous peine de manquement à nos obligations en tant que service public.<br>
		Merci de faire le nécessaire.<br><br>

		<u>Récapitulatif de la demande :</u><br>
		Numéro : <strong>'.$demande->id.'</strong><br>
		Date d\'envoi : <strong>'.$demande->horodateur.'</strong><br>
		Localisation : <strong>'.htmlspecialchars($localisation, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>
		Objet : <strong>'.htmlspecialchars($demande->objet, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>
		Message : <strong><br>'.nl2br(htmlspecialchars($demande->message, ENT_NOQUOTES, 'UTF-8', FALSE)).'</strong><br><br>

		Cordialement<br><br>

		La Direction
		</body>
		</html>';

		return $message;
	}

	private function messageDemandeCitoyenSiteATraiter($demande, $exceptions)
	{
		$message = '
		<!DOCTYPE html>
		<html>
		<head>
		</head>
		<body style="font-family:verdana;font-size:100%;">
		Bonjour,<br><br>

		Une demande citoyen a été tranférée à votre Service.<br>
		Elle devra être traitée dans les 2 mois, sous peine de manquement à nos obligations en tant que service public.<br>
		Merci de faire le nécessaire.<br><br>

		<u>Récapitulatif de la demande :</u><br>
		Numéro : <strong>'.$demande['id'].'</strong><br>
		Date d\'envoi : <strong>'.$demande['horodateur'].'</strong><br><br>';

		foreach ($demande as $key => $value) {
			if ( ! in_array($key, $exceptions) )
				$message .= $key.' : <strong>'.htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE).'</strong><br>';
		}
		$message .= '<br>

		Accéder à la <a href="'.site_url().'/citoyen/demande_citoyen_site_detail/'.$demande['id'].'">demande</a>.<br><br>

		Cordialement<br><br>

		La Direction
		</body>
		</html>';

		return $message;
	}
	/*
	*
	*
	*
	*
	*
	*
	* METHODES PRIVATE
	*
	*
	*
	*
	*
	*
	*
	*/
	private function convertirEnHeures($nombre_heures)
	{
		if ($nombre_heures == NULL)
			return '';

		$heures = (int) $nombre_heures;
		$minutes = fmod($nombre_heures, 1) * 15 / 0.25;

		if ($minutes == 0) $minutes = '00';

		return $heures.'h'.$minutes;
	}

}