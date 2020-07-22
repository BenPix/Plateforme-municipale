<div class="row py-2 justify-content-center">
	<div class="col-12 col-lg-6  col-md-8 ">

		<?php
		echo form_open('gestion/pole_update_go/'.$id);
		echo form_fieldset('<u>Modification du Pôle</u>');

		echo form_label('<u>Désignation : </u>');
		$designation = array(
			'name' => 'designation',
			'disabled' => TRUE,
			'value' => htmlspecialchars($pole->nom),
			'required' => '',
			'class' => 'form-control'
		);
		echo form_input($designation).'<br>';

		echo form_label('<u>Responsable : </u>');
		$attributs = array(
			'required' => '',
			'class' => 'form-control'
		);
		echo form_dropdown('responsable_id', html_escape($users), $pole->responsable_id, $attributs).'<br>';

		echo '<div style="display:'.$module_interservices_or_citoyen_activated.'">';
		echo form_label('<u>Peut être sollicité par des demandes</u>');
		echo form_dropdown('sollicitable_via_interservices', array(0 => 'Non', 1 => 'Oui'), set_value('sollicitable_via_interservices', $pole->sollicitable, FALSE), $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_interservices_activated.'">';
		echo form_label('<u>Ce pôle est-il confidentiel ?</u>');
		echo form_dropdown('confidentialite', array(0 => 'Non', 1 => 'Oui'), set_value('confidentialite', $pole->confidentialite, FALSE), $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_bon_de_commande_activated.'">';
		echo form_label('<u>Ce pôle nécessite-t-il de créer des bons de commande ?</u>');
		echo form_dropdown('bdc', $sujet_aux_bdc, $pole->bdc, $attributs).'<br>';
		echo form_dropdown('bdc', array(0 => 'Non', 1 => 'Oui'), set_value('bdc', $pole->confidentialite, FALSE), $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_news_activated.'">';
		echo form_label('<u>A quelle catégorie appartient ce pôle ?</u>');
		echo form_dropdown('categorie', html_escape($categories), set_value('categorie', $pole->categorie_id, FALSE), $attributs).'<br>';
		echo '</div>';

		$modifier = array(
			'name' => 'modifier',
			'value' => 'Modifier',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($modifier);

		echo form_close();
		?>

		<br><br>
		<hr>
		<br>
		<?php
		echo '<div style="display:'.$module_interservices_activated.'">';
		echo form_open('gestion/pole_sous_responsable/'.$id);

		echo form_label('Vous pouvez attribuer un accès spécial à un agent, lui permettant d\'accéder aux demandes d\'un Pôle supplémentaire, dans ce cas : '.htmlspecialchars($pole->nom).'.').'<br>(Uniquement pour les demandes interservices envoyées.)<br><br>';
		echo form_label('<u>L\'agent : </u>');
		echo form_dropdown('sous_responsable_potentiel_id', html_escape($sous_responsables_potentiels), '', array('class' => 'form-control')).'<br>';

		$attribuer = array(
			'name' => 'attribuer',
			'value' => 'Attribuer',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($attribuer).'<br><br>';

		echo form_label('<u>Retirer l\'accès spécial de l\'agent : </u>');
		echo form_dropdown('sous_responsable_id', html_escape($sous_responsables), '', array('class' => 'form-control')).'<br>';

		$retirer = array(
			'name' => 'retirer',
			'value' => 'Retirer',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($retirer);

		echo form_close();
		echo '</div>';
		?>
	</div>
</div>
<br>