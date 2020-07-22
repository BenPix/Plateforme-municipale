<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Lien_model extends CI_Model
{

	// lien qui permet de delete la news
	public function delete_news_debut()
	{
		return '<a href="'.base_url().'index.php/accueil/delete_news/';
	}
	public function delete_news_fin()
	{
		return '"><i class="material-icons" style="float:right;font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table jour_ferie colonne delete
	// permet de delete le jour ferie
	public function delete_jour_ferie_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/delete_jour_ferie/';
	}
	public function delete_jour_ferie_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table demande_conges colonne piece_jointe
	// permet de download la pièce jointe en rapport avec la demande de congé
	public function download_conges_debut()
	{
		return '<a href="'.base_url().'index.php/conges/download_conges/';
	}
	public function download_conges_fin()
	{
		return '"><i class="material-icons">get_app</i></a>';
	}

	// lien utilisé dans la bdd table demande_conges colonne piece_jointe
	// permet d'upload la pièce jointe en rapport avec la demande de congé
	public function upload_conges_debut()
	{
		return '<a href="'.base_url().'index.php/conges/upload/';
	}
	public function upload_conges_fin()
	{
		return '">'.img('icone_upload.png','','height: 20px;width: auto;').'</a>';
	}

	// lien utilisé dans la bdd table demande_conges colonne lien_validation
	// permet d'accéder a la page de confirmation de la validation en rapport avec la demande de congé
	public function valider_conges_debut()
	{
		return '<a href="'.base_url().'index.php/validation/traitement_conges/';
	}
	public function valider_conges_fin()
	{
		return '/validation"><i class="material-icons" style="font-size:30px;color:green">done</i></a>';
	}

	// lien utilisé dans la bdd table demande_conges colonne lien_refus
	// permet d'accéder a la page de confirmation du refus en rapport avec la demande de congé
	public function refuser_conges_debut()
	{
		return '<a href="'.base_url().'index.php/validation/traitement_conges/';
	}
	public function refuser_conges_fin()
	{
		return '/refus"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table demande_conges colonne lien_refus
	// permet d'accéder a la page de confirmation de la suppression en rapport avec la demande de congé
	public function delete_conges_debut()
	{
		return '<a href="'.base_url().'index.php/conges/delete_conges/';
	}
	public function delete_conges_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne selection
	// permet d'accéder à l'utilisateur et de poser un congé à sa place
	public function profil_debut()
	{
		return '<a href="'.base_url().'index.php/conges/demande_conges_RH/';
	}
	public function profil_fin()
	{
		return '"><i class="material-icons">account_circle</i></a>';
	}

	// permet d'accéder à l'utilisateur et de créditer/débiter/màj/supp du compte épargne temps ou de créer le compte
	public function creer_cet_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/creer_epargne_temps/';
	}
	public function maj_cet_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/maj_epargne_temps/';
	}
	public function crediter_cet_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/crediter_epargne_temps/';
	}
	public function debiter_cet_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/debiter_epargne_temps/';
	}
	public function cet_fin()
	{
		return '"><i class="material-icons">account_circle</i></a>';
	}
	public function exporter_cet_debut()
	{
		return '<a target="_blank" href="'.base_url().'index.php/gestion/exporter_epargne_temps/';
	}
	public function exporter_cet_fin()
	{
		return '"><i class="material-icons">get_app</i></a>';
	}
	public function delete_cet_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/delete_cet/';
	}
	public function delete_cet_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne lien_modification
	// permet d'accéder a la page de confirmation de la modification du profil
	public function modifier_profil_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/update_user/';
	}
	public function modifier_profil_fin()
	{
		return '"><i class="material-icons">update</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne lien_modification
	// permet d'accéder a la page de confirmation de la modification + réactivation du profil
	public function modifier_et_reactiver_profil_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/update_and_reactivate_user/';
	}
	public function modifier_et_reactiver_profil_fin()
	{
		return '"><i class="material-icons">update</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne lien_suppression
	// permet d'accéder a la page de confirmation de la suppression du profil
	public function supprimer_profil_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/delete_user/';
	}
	public function supprimer_profil_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la table historique colonne pieces_jointes
	// permet de download la/les pièce(s) jointe(s) en rapport avec la demande interservices
	public function download_interservice_debut()
	{
		return '<a href="'.base_url().'index.php/demande/download/';
	}
	public function download_interservice_fin()
	{
		return '"><i class="material-icons">get_app</i></a>';
	}

	// lien utilisé dans la table validation
	// permet d'accéder a la page de confirmation de la validation en rapport avec la demande interservices
	public function valider_demande_debut()
	{
		return '<a href="'.base_url().'index.php/validation/validation_demande/';
	}
	public function valider_demande_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:green">done</i></a>';
	}

	// lien utilisé dans la table stats demandes au niveau du numéro de dossier
	// permet d'accéder a la page qui détaille la demande interservices
	public function details_demande_debut()
	{
		return '<a href="'.base_url().'index.php/demande/detail/';
	}
	public function details_demande_milieu()
	{
		return '"><strong><u style="font-size:18px">';
	}
	public function details_demande_fin()
	{
		return '</u></strong></a>';
	}

	// lien utilisé dans la table stats demandes au niveau du numéro de dossier
	// permet d'accéder a la page qui détaille la demande interservices
	public function details_demande_terminee_debut()
	{
		return '<a href="'.base_url().'index.php/demande/detail_final/';
	}
	public function details_demande_terminee_milieu()
	{
		return '"><strong><u style="font-size:18px">';
	}
	public function details_demande_terminee_fin()
	{
		return '</u></strong></a>';
	}

	// lien utilisé dans la table validation
	// permet d'accéder a la page de confirmation du refus en rapport avec la demande interservices
	public function refuser_demande_debut()
	{
		return '<a href="'.base_url().'index.php/validation/refus_demande/';
	}
	public function refuser_demande_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne lien_validation
	// permet d'accéder a la page de confirmation de la validation en rapport à l'inscription
	public function valider_inscription_debut()
	{
		return '<a href="'.base_url().'index.php/validation/validation_inscription/';
	}
	public function valider_inscription_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:green">done</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne lien_refus
	// permet d'accéder a la page de confirmation du refus en rapport à l'inscription
	public function refuser_inscription_debut()
	{
		return '<a href="'.base_url().'index.php/validation/refus_inscription/';
	}
	public function refuser_inscription_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne maj
	// permet d'accéder a la page de modification du pole
	public function update_pole_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/pole_update/';
	}
	public function update_pole_fin()
	{
		return '"><i class="material-icons">update</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne suppression
	// permet d'accéder a la page de de suppression du pole
	public function delete_pole_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/pole_delete/';
	}
	public function delete_pole_fin()
	{
		return '"><i class="material-icons" style="color:red">delete</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne maj
	// permet d'accéder a la page de modification du pole
	public function update_sous_pole_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/sous_pole_update/';
	}
	public function update_sous_pole_fin()
	{
		return '"><i class="material-icons">update</i></a>';
	}

	// lien utilisé dans la bdd table utilisateur colonne suppression
	// permet d'accéder a la page de de suppression du pole
	public function delete_sous_pole_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/sous_pole_delete/';
	}
	public function delete_sous_pole_fin()
	{
		return '"><i class="material-icons" style="color:red">delete</i></a>';
	}

	// lien qui permet de delete la news
	public function previsualiser_news_debut()
	{
		return '<a href="'.base_url().'index.php/validation/previsualiser_news/';
	}
	public function previsualiser_news_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// lien qui permet de delete un acces special aux categories d'articles
	public function delete_acces_special_debut()
	{
		return '<a href="'.base_url().'index.php/accueil/enlever_acces_special/';
	}
	public function delete_acces_special_fin()
	{
		return '"><i class="material-icons" style="color:red">delete</i></a>';
	}

	// lien qui permet de delete un acces special aux categories d'articles
	public function delete_categorie_debut()
	{
		return '<a href="'.base_url().'index.php/accueil/delete_categorie/';
	}
	public function delete_categorie_fin()
	{
		return '"><i class="material-icons" style="color:red">delete</i></a>';
	}

	// permet d'accéder a la page de détail du bon de commande
	public function detail_bdc_debut()
	{
		return '<a href="'.base_url().'index.php/validation/detail_bon_de_commande/';
	}
	public function detail_bdc_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail du bon de commande
	public function detail_bdc_debut_prior()
	{
		return '<a href="'.base_url().'index.php/validation/detail_bon_de_commande/';
	}
	public function detail_bdc_fin_prior()
	{
		return '/prior"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail du bon de commande
	public function recap_bdc_debut()
	{
		return '<a href="'.base_url().'index.php/documents/recapitulatif_bon_de_commande/';
	}
	public function recap_bdc_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail du bon de commande
	public function detail_bdc_en_cours_debut()
	{
		return '<a href="'.base_url().'index.php/documents/detail_bon_de_commande/';
	}
	public function detail_bdc_en_cours_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de confirmation de la validation en rapport avec la demande d'heures supp
	public function valider_heures_supp_debut()
	{
		return '<a href="'.base_url().'index.php/validation/detail_heures_supp/';
	}
	public function valider_heures_supp_fin()
	{
		return '/validation"><i class="material-icons" style="font-size:30px;color:green">done</i></a>';
	}

	// permet d'accéder a la page de confirmation du refus en rapport avec la demande d'heures supp
	public function refuser_heures_supp_debut()
	{
		return '<a href="'.base_url().'index.php/validation/detail_heures_supp/';
	}
	public function refuser_heures_supp_fin()
	{
		return '/refus"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen pour les validation
	public function detail_demande_citoyen_a_valider_app_cfdb7_debut()
	{
		return '<a href="'.base_url().'index.php/validation/demande_citoyen_detail/app_cfdb7/';
	}
	public function detail_demande_citoyen_a_valider_app_cfdb7_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	public function detail_demande_citoyen_a_valider_app_cfdb7_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen
	public function detail_demande_citoyen_app_cfdb7_debut()
	{
		return '<a href="'.base_url().'index.php/citoyen/demande_citoyen_detail/app_cfdb7/';
	}
	public function detail_demande_citoyen_app_cfdb7_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	// idem que detail_demande_citoyen_debut() mais avec une icone rouge pour la visibilité de l'urgence
	public function detail_demande_citoyen_app_cfdb7_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen du site qui a été validée
	public function detail_demande_citoyen_site_debut()
	{
		return '<a href="'.base_url().'index.php/citoyen/demande_citoyen_site_detail/';
	}
	public function detail_demande_citoyen_site_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	// idem que detail_demande_citoyen_site_debut() mais avec une icone rouge pour la visibilité de l'urgence
	public function detail_demande_citoyen_site_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen du site pour validation
	public function detail_demande_citoyen_site_a_valider_debut()
	{
		return '<a href="'.base_url().'index.php/validation/demande_citoyen_site_detail/';
	}
	public function detail_demande_citoyen_site_a_valider_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	// idem que detail_demande_citoyen_site_debut() mais avec une icone rouge pour la visibilité de l'urgence
	public function detail_demande_citoyen_site_a_valider_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen du site qui a été validée
	public function detail_demande_citoyen_site_cfdb7_debut()
	{
		return '<a href="'.base_url().'index.php/citoyen/demande_citoyen_detail/site_cfdb7/';
	}
	public function detail_demande_citoyen_site_cfdb7_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	// idem que detail_demande_citoyen_site_debut() mais avec une icone rouge pour la visibilité de l'urgence
	public function detail_demande_citoyen_site_cfdb7_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demande citoyen du site pour validation
	public function detail_demande_citoyen_a_valider_site_cfdb7_debut()
	{
		return '<a href="'.base_url().'index.php/validation/demande_citoyen_detail/site_cfdb7/';
	}
	public function detail_demande_citoyen_a_valider_site_cfdb7_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}
	// idem que detail_demande_citoyen_site_debut() mais avec une icone rouge pour la visibilité de l'urgence
	public function detail_demande_citoyen_a_valider_site_cfdb7_fin_rouge()
	{
		return '"><i class="material-icons" style="font-size:50px; color:red;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail des congés et heures supp de l'agent
	public function detail_conges_agent_debut()
	{
		return '<a href="'.base_url().'index.php/conges/detail_conges_personnel/';
	}
	public function detail_conges_agent_milieu()
	{
		return '" target="_blank">';
	}
	public function detail_conges_agent_fin()
	{
		return '</a>';
	}

	// permet d'accéder a la page de détail d'un document délibération/arreté/décision
	public function detail_delib_debut()
	{
		return '<a href="'.base_url().'index.php/documents/detail_deliberation/';
	}
	public function detail_arrete_debut()
	{
		return '<a href="'.base_url().'index.php/documents/detail_arrete/';
	}
	public function detail_decision_debut()
	{
		return '<a href="'.base_url().'index.php/documents/detail_decision/';
	}
	public function detail_document_fin()
	{
		return '" target="_blank"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail du courrier
	public function detail_courrier_debut()
	{
		return '<a href="'.base_url().'index.php/courrier/detail/';
	}
	public function detail_courrier_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet d'accéder a la page de détail de la demadne interservices du courrier
	public function detail_courrier_interservices_debut()
	{
		return '<a href="'.base_url().'index.php/demande/detail/';
	}
	public function detail_courrier_interservices_milieu()
	{
		return '" style="font-size:20px;">N°';
	}
	public function detail_courrier_interservices_fin()
	{
		return '</a>';
	}

	// permet de delete l'acces au courrier pour un agent
	public function delete_acces_courrier_debut()
	{
		return '<a href="'.base_url().'index.php/courrier/supprimer_acces/';
	}
	public function delete_acces_courrier_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// permet de réactiver un motif de congés exceptionnels qui a été supprimé (=inactivé)
	public function reactiver_motif_conges_exceptionnels_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/reactiver_motif_go/';
	}
	public function reactiver_motif_conges_exceptionnels_fin()
	{
		return '"><i class="material-icons">restore</i></a>';
	}

	// permet de delete un type de contrat (ou plutot l'inactiver)
	public function delete_type_contrat_debut()
	{
		return '<a href="'.base_url().'index.php/gestion/delete_type_contrat/';
	}
	public function delete_type_contrat_fin()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

	// permet d'accéder a la page de détail de la note
	public function detail_note_debut()
	{
		return '<a href="'.base_url().'index.php/note/detail/';
	}
	public function detail_note_fin()
	{
		return '"><i class="material-icons" style="font-size:50px;">visibility</i></a>';
	}

	// permet de delete une etape du workflow de la note
	public function delete_workflow_step_start()
	{
		return '<a href="'.base_url().'index.php/note/delete_workflow/';
	}
	public function delete_workflow_step_end()
	{
		return '"><i class="material-icons" style="font-size:30px;color:red">delete</i></a>';
	}

}