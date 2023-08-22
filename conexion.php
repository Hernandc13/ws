<?php
	$host="db-moodle-subitus-do-user-1196230-0.b.db.ondigitalocean.com";
	$port="25060";
	$socket="";
	$user="moodleuser";
	$password="qlwGlJt5nAChOIZz";
	$dbname="moodle_toyota";
	
	
	$conexion = new mysqli($host, $user, $password, $dbname, $port, $socket)
		or die ('Could not connect to the database server' . mysqli_connect_error());

?>
