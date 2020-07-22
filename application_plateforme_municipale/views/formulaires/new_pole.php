<div class="row py-2 justify-content-center">
	<div class="col-12 col-lg-6  col-md-8 ">

		<?php
		echo form_open('gestion/pole_create');
		echo form_fieldset('<u>Nouveau Pôle</u>');

		echo form_label('<u>Désignation : </u>');
		$designation = array(
			'name' => 'designation',
			'value' => set_value('designation'),
			'required' => '',
			'class' => 'form-control'
		);
		echo form_input($designation).'<br>';

		echo form_label('<u>Responsable : </u>');
		$responsable = array(
			'required' => '',
			'class' => 'form-control'
		);
		echo form_dropdown('responsable_id', html_escape($users), '', $responsable).'<br>';

		echo '<div style="display:'.$module_interservices_or_citoyen_activated.'">';
		echo form_label('<u>Peut être sollicité par des demandes</u>');
		$attributs = array(
            'required' => '',
            'class' => 'form-control'
        );
		echo form_dropdown('sollicitable_via_interservices', array(0 => 'Non', 1 => 'Oui'), 1, $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_interservices_activated.'">';
		echo form_label('<u>Ce pôle est-il confidentiel ?</u>');
		echo form_dropdown('confidentialite', array(0 => 'Non', 1 => 'Oui'), 0, $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_bon_de_commande_activated.'">';
		echo form_label('<u>Ce pôle nécessite-t-il de créer des bons de commande ?</u>');
		echo form_dropdown('bdc', array(0 => 'Non', 1 => 'Oui'), 0, $attributs).'<br>';
		echo '</div>';

		echo '<div style="display:'.$module_news_activated.'">';
		echo form_label('<u>A quelle catégorie appartient ce pôle ?</u>');
		echo form_dropdown('categorie', html_escape($categories), 1, $attributs).'<br>';
		echo '</div>';

		$ajouter = array(
			'name' => 'ajouter',
			'value' => 'Ajouter',
			'type' => 'submit',
			'class' => 'btn btn-primary  display-4'
		);
		echo form_submit($ajouter);

		echo form_close();
		?>
	</div>
</div>
<br>