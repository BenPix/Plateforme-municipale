<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $titre . '  ♦ ' ?>Sains-en-Gohelle</title>
	<link rel="shortcut icon" href="<?php echo img_url($logo_icone) ?>" type="image/x-icon">
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
		google.charts.load('current', {'packages':['corechart']});
		google.charts.setOnLoadCallback(drawChart);

		function drawChart() {

			var data = google.visualization.arrayToDataTable([
				['<?php echo $colonne ?>', 'Nombre de demandes']
				<?php
				foreach ($graph as $row) {
					echo ',[\'' . $row->portion . '\', ' . $row->total . ']';
				}
				?>
				]);

			var options = {
				title: 'Portions de demandes'
			};

			var chart = new google.visualization.PieChart(document.getElementById('piechart'));

			chart.draw(data, options);
		}
	</script>
</head>
<body>
	<div id="piechart" style="width: 1500px; height: 800px;"></div>
</body>
</html>