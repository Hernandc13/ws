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
				$sqlValidaCalificacion = "SELECT usr.email, usr.id ,gi.itemmodule AS 'tipo_actividad',cs.section AS 'numero_seccion',cs.name as 'nombre_seccion'
				, FROM_UNIXTIME(gg.timemodified,'%M %D, %Y') AS 'fecha' ,ROUND(gg.rawgrademax,0) as 'peso'
				,ROUND(gg.finalgrade ,0) as 'calificacion' ,cm.id as 'link', gi.itemname AS 'actividad'
				,if (gi.locked > 0,'Bloqueda' , 'Activa') as 'estatus' FROM mdl_course AS c
				LEFT JOIN mdl_course_sections AS cs ON cs.course = c.id AND cs.section > 0 AND cs.section <=14 
				LEFT JOIN mdl_course_modules AS cm ON cm.course = c.id AND cm.section = cs.id
				LEFT JOIN mdl_assign AS asg ON asg.id = cm.instance
				JOIN mdl_grade_items AS gi ON gi.iteminstance = cm.instance AND gi.gradetype = 1 AND gi.hidden != 1 AND gi.courseid = c.id AND cm.course = c.id AND cm.section = cs.id
				JOIN mdl_grade_grades as gg on gg.itemid=gi.id join mdl_user as usr on usr.id=gg.userid
				WHERE usr.email='$username' and c.id =4;";
               $resultcalf = mysqli_query($conexion, $sqlValidaCalificacion);
			   if(mysqli_num_rows($resultcalf)>0)
			   {
				if($row = $resultcalf->fetch_assoc()) {
					$cal=$row["calificacion"];
					if($cal>=8 and $cal<=10){
						header("Location:./Response.php?res=Ya realizaste la evaluación!&credenciales=<b style='color:red;'>Tu calificación fue de : $cal </b> espera que aprueben tu solicitud.");
					}else{
						header("Location:./Response.php?res=Ya hiciste la evaluacion pero tu calificación no es aprobatoria.");
					}
				}

			   }else{
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
		$usrre=$row["username"];
		header("Location:./Response.php?res=El usuario ya esta registrado!&credenciales=Da click en el botón y realiza la evaluación tu usuario es: <b style='color:red;'> $usrre </b> y tu contrseña es : <b style='color:red;'>$pass</b>");

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
		$usrre=$row["username"];
		header("Location:./Response.php?res=Se registro correctamente!&credenciales=Da click en el botón y realiza la evaluación tu usuario es: <b style='color:red;'> $usrre </b> y tu contrseña es : <b style='color:red;'>$pass</b>");
			}
		}
			} else {
				header("Location:./Response.php?war=No se pudo registrar en  ASSIGNMENTS.");
			}
	 }
		}
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
				$usrre=$row["username"];
		header("Location:./Response.php?res=Se registro al usuario correctamente!&credenciales=Da click en el botón y realiza la evaluación tu usuario es: <b style='color:red;'> $usrre </b> y tu contrseña es : <b style='color:red;'>$pass</b>");
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
			   }

	   
}else{
	header("Location:./Response.php?error=No llego la información.");
}
 ?>