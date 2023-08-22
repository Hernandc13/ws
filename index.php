<?php
   /*
	   Web Service
	   Carmelo Hernández
   */

include 'conexion.php';
$username=$_GET['username'];
$pass = $_GET['pass'];
$passHash = password_hash($pass, PASSWORD_BCRYPT);
$lastname=$_GET['lastname'];
$firstname=$_GET['firstname'];
$email=$_GET['email'];
		//Se valida que toda la información llegue para poder hacer la petición a base de datos.
            if(isset($username) and isset($pass) and isset($lastname) and isset($firstname) and isset($email)){
	            $sql = "select * from mdl_user where username='$username' or email='$email'";
	            $result = mysqli_query($conexion, $sql);
	             if(mysqli_num_rows($result)>0)
	            {
			        if($row = $result->fetch_assoc()) {
			            $idRegistrado=$row["id"];
				     	//Necesitamos ver si el curso esta registrado en la bd ENROL
	                    	$idcourse='4';
		               $sqlCurse = "SELECT id FROM mdl_enrol WHERE courseid=$idcourse AND enrol='manual'";
	                   $resultC = mysqli_query($conexion, $sqlCurse);
	           	if(!$resultC){
					header("Location:./Response.php?war=No existe en ENROL.");
	                     	}
		if($row = $resultC->fetch_assoc()) {
			$idenrol = $row["id"];
			ECHO $idenrol;
		}
		//Ahora necesitamos el contexto
		$sqlCtx = "SELECT id FROM mdl_context WHERE contextlevel=50 AND instanceid=$idcourse";
		$resultCtx = mysqli_query($conexion, $sqlCtx);
		if(!$resultCtx){
			header("Location:./Response.php?war=No existe en el CONTEXTO.");
		}
		if($row = $resultCtx->fetch_assoc()) {
			$idcontext = $row["id"];
			ECHO $idcontext;
		}
		//Se empieza a registrar al usuario al curso
		//Se valida si ya esta registrado en el curso el usuario
		$sqlValida = "select * from mdl_user_enrolments where userid='$idRegistrado' and enrolid='$idenrol'";
	$result = mysqli_query($conexion, $sqlValida);
	if(mysqli_num_rows($result)>0)
	 {
		$sql = "select * from mdl_user where username='$username' or email='$email'";
		$result = mysqli_query($conexion, $sql);
		 if(mysqli_num_rows($result)>0)
		{
			if($row = $result->fetch_assoc()) {
		//Si el usuario ya esta registrado en el curso solo le manda su usuario y que ingrese a su cuenta
		//suponiendo que si ya esta registrado ya sabe su contraseña.
		//echo "EL USUARIO YA ESTA REGISTRADO EN EL CURSO";
		//echo "<h2>Ingresamoodle usa la contraseña de tu usuario para iniciar sesión.</h2>";
		$usrre=$row["username"];
		//echo "<h2>".$row["password"]."</h2>";
		//echo "<button><a href='https://tmmgt.safetylearning.mx/'>Inicia Sesión</a></button>";
		header("Location:./Response.php?res=El usuario se registro correctamente,da click en el botón e inicia sesión tu usuario es: $usrre.");
			}
		}
			
	 }else{
			//Empezamos a registrar al usuario al curso.
			$time = time();
			$ntime = $time + 60*60*24*0; //How long will it last enroled $duration = days, this can be 0 for unlimited.
			$sqlU = "INSERT INTO mdl_user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
			VALUES (0, $idenrol, $idRegistrado, '$time', '$ntime', '$time', '$time')";
			if ($conexion->query($sqlU) === TRUE) {
			} else {
			   header("Location:./Response.php?war=No se pudo registrar en  ENROLMENTS.");
			}
			$sqlR = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, timemodified)
			VALUES (5, $idcontext, '$idRegistrado', '$time')"; //Roleid = 5, means student.
			if ($conexion->query($sqlR) === TRUE) {
				$sql = "select * from mdl_user where username='$username' or email='$email'";
		$result = mysqli_query($conexion, $sql);
		 if(mysqli_num_rows($result)>0)
		{
			if($row = $result->fetch_assoc()) {
		//Si el usuario ya esta registrado en el curso solo le manda su usuario y que ingrese a su cuenta
		//suponiendo que si ya esta registrado ya sabe su contraseña.
		//echo "EL USUARIO YA ESTA REGISTRADO EN EL CURSO";
		//echo "<h2>Ingresamoodle usa la contraseña de tu usuario para iniciar sesión.</h2>";
		$usrre=$row["username"];
		//echo "<h2>".$row["password"]."</h2>";
		//echo "<button><a href='https://tmmgt.safetylearning.mx/'>Inicia Sesión</a></button>";
		header("Location:./Response.php?res=El usuario se registro correctamente,da click en el botón e inicia sesión tu usuario es: $usrre.");
			}
		}
			} else {
				header("Location:./Response.php?war=No se pudo registrar en  ASSIGNMENTS.");
			}
	 }
		}
		header("Location:./Response.php?res=El usuario ya esta registrado, da click en el botón e inicia sesión.");
	 }else{
		$sqlinsert = "INSERT INTO mdl_user (auth,username,password,lastname,firstname,email,confirmed,mnethostid,city,country,lang,timezone,maildisplay)
VALUES('manual','$username','$passHash','$lastname','$firstname','$email','1','1','mexico','mx','es_mx','America/Monterrey','1');";
	if ($conexion->query($sqlinsert) === TRUE) {
		header("Location:./Response.php?res=Se registro el usuario correctamente.");
		$sql = "select * from mdl_user where username='$username' or email='$email'";
	$result = mysqli_query($conexion, $sql);
	if(mysqli_num_rows($result)>0)
	 {
			if($row = $result->fetch_assoc()) {
			$idRegistrado=$row["id"];
			echo "<hr> Id: " . $row["id"] . "-Nombre Usuario: " . $row["firstname"] . "<hr>";
					//Necesitamos ver si el curso esta registrado en la bd ENROL
		$idcourse='4';
		$sqlCurse = "SELECT id FROM mdl_enrol WHERE courseid=$idcourse AND enrol='manual'";
		$resultC = mysqli_query($conexion, $sqlCurse);
		if(!$resultC){
			echo "nO existe";
		}
		if($row = $resultC->fetch_assoc()) {
			$idenrol = $row["id"];
			ECHO $idenrol;
		}
		//Ahora necesitamos el contexto
		$sqlCtx = "SELECT id FROM mdl_context WHERE contextlevel=50 AND instanceid=$idcourse";
		$resultCtx = mysqli_query($conexion, $sqlCtx);
		if(!$resultCtx){
			echo "nO existe";
		}
		if($row = $resultCtx->fetch_assoc()) {
			$idcontext = $row["id"];
			ECHO $idcontext;
		}
		//Primero validamos que no este registrado ya en el curso
		$sqlValida = "select * from mdl_user_enrolments where userid='$idRegistrado' and enrolid='$idenrol'";
	$result = mysqli_query($conexion, $sqlValida);
	if(mysqli_num_rows($result)>0)
	 {
		header("Location:./Response.php?error=El usuario ya esta registrado en el curso.");
	 }else{
			//Empezamos a registrar al usuario al curso.
			$time = time();
			$ntime = $time + 60*60*24*0; 
			$sqlU = "INSERT INTO mdl_user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
			VALUES (0, $idenrol, $idRegistrado, '$time', '$ntime', '$time', '$time')";
			if ($conexion->query($sqlU) === TRUE) {
			} else {
			   header("Location:./Response.php?war=No se pudo registrar en  ENROLMENTS.");
			}
			$sqlR = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, timemodified)
			VALUES (5, $idcontext, '$idRegistrado', '$time')"; //Roleid = 5, means student.
			if ($conexion->query($sqlR) === TRUE) {
				$sql = "select * from mdl_user where username='$username' or email='$email'";
		$result = mysqli_query($conexion, $sql);
		 if(mysqli_num_rows($result)>0)
		{
			if($row = $result->fetch_assoc()) {
		//Si el usuario ya esta registrado en el curso solo le manda su usuario y que ingrese a su cuenta
		//suponiendo que si ya esta registrado ya sabe su contraseña.
		//echo "EL USUARIO YA ESTA REGISTRADO EN EL CURSO";
		//echo "<h2>Ingresamoodle usa la contraseña de tu usuario para iniciar sesión.</h2>";
        $usrre=$row["username"];
		//echo "<h2>".$row["password"]."</h2>";
		//echo "<button><a href='https://tmmgt.safetylearning.mx/'>Inicia Sesión</a></button>";
		header("Location:./Response.php?res=El usuario se registro correctamente,da click en el botón e inicia sesión tu usuario es: $usrre y tu contraseña es: $pass");
			}
		}
			} else {
				header("Location:./Response.php?war=No se pudo registrar en  ASSIGNMENTS.");
			}
	 }
		}
	 }
	} else {
		echo $conexion->error;
	}
	$conexion->close();
	 }
}else{
	header("Location:./Response.php?error=No llego la información.");
}
 ?>