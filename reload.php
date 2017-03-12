<?php

	session_start();

	if(isset($_POST['sessionIdentifier']) and
		$_POST['sessionIdentifier'] !== NULL and
		$_POST['sessionIdentifier'] !== ''
	) {
		$_SESSION['sessionIdentifier'] = $_POST['sessionIdentifier'];
	}

	if(isset($_SESSION['sessionIdentifier']) and
		$_SESSION['sessionIdentifier'] !== NULL and
		$_SESSION['sessionIdentifier'] !== ''
	) {

		echo '<html>
				<head>
					<title>Nainw[hack] Reloader bÃªta</title>
					<script type="text/javascript" src="media/js/jquery-1.5.1.js"></script>
					<link rel="stylesheet" type="text/css" href="media/css/console.css" />
				</head>

				<body>';

		$jsMaFace = '<script>
				var refreshId = setInterval(function() {
					$("#content").load("load.php");
				}, 3000);
			</script>';

		echo '<h1>Nainw<i>[hack]</i> Reloader <i>bêta</i></h1>
			<br/><br/>
			<div id="content">
			</div>
			<h2>Nainw<i>[hack]</i> Reloader <i>bêta</i></h2>';

		echo $jsMaFace;

		echo '	</body>';
		echo '</html>';

	}

?>

