<?php

# Send Javascript header 
header('Content-type: text/javascript');

# Load Config 
include_once('../../config.php');


// Include & load the variables 
// ############################

$variables 	= realpath(dirname(__FILE__)).'/../json/variables.json';
$config 	= json_decode(file_get_contents($variables)); 


# Connect MySQL 
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if($mysqli->connect_error != ''){exit('Error MySQL Connect');}

# Chart Graph datas  

$trainer_lvl = [];
# For all 3 teams
for ($teamid = 1; $teamid <= 3; $teamid++) {
	$req    = "SELECT level, count(level) AS count FROM trainer WHERE team = '".$teamid."' GROUP BY level";
	if ($result = $mysqli->query($req)) {
		# build level=>count array
		$data = [];
		while ($row = $result->fetch_assoc()) {
			$data[$row["level"]] = $row["count"];
		}
		
		# only if data isn't empty
		if ($data) {
			# fill empty levels counts with 0
			for ($i = 5; $i <= 40; $i++) {
				if (!isset($data[$i])) {
					$data[$i]=0;
				}
			}
			# sort array again
			ksort($data);
			$trainer_lvl[$teamid] = $data;
		}
		
		$result->free();
	}
}
$mysqli->close();

// Hide trainer level graph if there is no result (gym-info not active in PokemonGo-Map)
// and only show javascript if it's set
if (!$trainer_lvl) { ?>
	document.getElementById('trainer_lvl_graph').style.display = 'none';
<?php } else { ?>
	// Global Options
	// --------------

	Chart.defaults.global.legend.display = false;

	// Trainer level
	// -------------

	var trainer_lvl = $("#trainer_lvl");

	var data_trainer_lvl = {
	    labels: [5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40],
	    datasets: [
		{
		    label: "Mystic trainer level count",
		    backgroundColor: "rgba(59,129,255,0.6)",
		    borderColor: "rgba(59,129,255,1)",
	            borderWidth: 1,
	            data: [<?php if (isset($trainer_lvl[1])) { echo implode(',', $trainer_lvl[1]); } ?>],
		},
		{
		    label: "Valor trainer level count",
		    backgroundColor: "rgba(247,10,20,0.6)",
		    borderColor: "rgba(247,10,20,1)",
	            borderWidth: 1,
		    data: [<?php  if (isset($trainer_lvl[2])) { echo implode(',', $trainer_lvl[2]); } ?>],
		},
		{
		    label: "Instinct trainer level count",
		    backgroundColor: "rgba(248,153,0,0.6)",
		    borderColor: "rgba(248,153,0,1)",
	            borderWidth: 1,
		    data: [<?php if (isset($trainer_lvl[3])) { echo implode(',', $trainer_lvl[3]); } ?>],
		}
	    ]
	};

	var myBarChart = new Chart(trainer_lvl, {
	    type: 'bar',
	    data: data_trainer_lvl,
	    options : ''
	});
<?php } ?>
