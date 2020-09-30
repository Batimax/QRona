<?php
include("functions.php");
$data_points = array();
//Best practice is to create a separate file for handling connection to database

$data_points = getDataDaily($dtb);

$dataPoints = array(
	array("x" => 946665000000, "y" => 3289000),
	array("x" => 978287400000, "y" => 3830000),
	array("x" => 1009823400000, "y" => 2009000),
	array("x" => 1041359400000, "y" => 2840000),
	array("x" => 1072895400000, "y" => 2396000),
	array("x" => 1104517800000, "y" => 1613000),
	array("x" => 1136053800000, "y" => 1821000),
	array("x" => 1167589800000, "y" => 2000000),
	array("x" => 1199125800000, "y" => 1397000),
	array("x" => 1230748200000, "y" => 2506000),
	array("x" => 1262284200000, "y" => 6704000),
	array("x" => 1293820200000, "y" => 5704000),
	array("x" => 1325356200000, "y" => 4009000),
	array("x" => 1356978600000, "y" => 3026000),
	array("x" => 1388514600000, "y" => 2394000),
	array("x" => 1420050600000, "y" => 1872000),
	array("x" => 1451586600000, "y" => 2140000)
);

print_r($data_points);
echo " ||||||||| ";
print_r($dataPoints);
?>

<!DOCTYPE HTML>
<html>

<head>
	<script>
		window.onload = function() {

			// Today

			// This week

			// This Month

			// This Year


			// Moyennes

			var chart = new CanvasJS.Chart("chartContainer", {
				animationEnabled: true,
				exportEnabled: true,
				theme: "light1", // "light1", "light2", "dark1", "dark2"
				title: {
					text: "PHP Column Chart from Database"
				},
				data: [{
					type: "line",
					name: "People",
					showInLegend: "true",
					xValueType: "dateTime",
					xValueFormatString: "H",
					// yValueFormatString: "₹#,##0.##",
					dataPoints: <?php echo json_encode($data_points, JSON_NUMERIC_CHECK); ?>
				}]

			});
			chart.render();

		}
	</script>
</head>

<body>
	<div id="chartContainer" style="height: 370px; width: 100%;"></div>
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>

</html>
