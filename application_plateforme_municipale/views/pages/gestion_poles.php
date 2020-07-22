<section class="mbr-section form3 cid-r1fWFz2mbZ" id="form3-c">

	<div class="row py-2 justify-content-center">
		<div class="col-12 col-lg-6  col-md-8 ">
			<div style="border: solid black 2px; background-color: #A9A9A9; font-size: 30px; text-align: center;"><?php echo $tab_title ?></div>
			<br><br><br><br>
		</div>
	</div>
	
	<div style="width: 60%;margin: 0 auto;">
		<?php
		echo validation_errors();

		$this->table->set_heading($heading);
		$tmpl = array(
			'table_open' => '<table border="solid" cellpadding="4" cellspacing="0" class="tab-pole">',
			'thead_open' => '<thead style="background-color: #DCDCDC; border: solid black 2px;">'
		);

		$this->table->set_template($tmpl);

		$this->table->function = 'htmlspecialchars';

		$this->table->set_columns_for_callback_function($escaped_columns);

		echo $this->table->generate($tab);
		?>
	</div>

</section>