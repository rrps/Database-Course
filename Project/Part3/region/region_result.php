<?php
	session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>SIBD Project</title>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
    	<link rel="stylesheet" type="text/css" href="../css/table_style.css">
    </head>
    <body>
<?php include '../header.php';?>
<?php include '../database/db.php';?>
<?php
	
	$s_id = $_SESSION['series_id'];
	$e_index = $_SESSION['e_ind'];
	
	$x1 = $_POST['x1'];
	$x2 = $_POST['x2'];
	$y1 = $_POST['y1'];
	$y2 = $_POST['y2'];

	$result_validation = $connection->prepare(
		"SELECT region_overlaps_element(:s_id, :e_index, :x1, :y1, :x2, :y2) as verify");
	$result_validation->bindParam(':s_id', $s_id);
	$result_validation->bindParam(':e_index', $e_index);
	$result_validation->bindParam(':x1', $x1);
	$result_validation->bindParam(':y1', $y1);
	$result_validation->bindParam(':x2', $x2);
	$result_validation->bindParam(':y2', $y2);
	$result_validation->execute();
	if ($result_validation == FALSE) {
        $info = $connection->errorInfo();
        echo("<p>ERROR: {$info[0]} {$info[1]} {$info[2]}</p>");
        exit(0);
    }  
	foreach($result_validation as $row){
		$validation = $row['verify'];
	}

	$sql = "SELECT p.p_number, p.name as p_name, req.r_number, s.name as s_name,st.doctor_id 
			FROM Patient as p, Request as req , Study as st , Series as s 
			WHERE p.p_number = req.patient_id 
			AND req.r_number = st.request_number 
			AND st.request_number = s.request_number 
			AND st.description = s.description 
			AND s.series_id = $s_id";
	$result = $connection->query($sql);
	if ($result== FALSE) {
        $info = $connection->errorInfo();
        echo("<p>ERROR: {$info[0]} {$info[1]} {$info[2]}</p>");
        exit(0);
    }  

	if($validation == "0"){
		$result_insert = $connection->prepare(
			"INSERT INTO Region values(:s_id, :e_index, :x1, :y1, :x2, :y2)");
		$result_insert->bindParam(':s_id', $s_id);
		$result_insert->bindParam(':e_index', $e_index);
		$result_insert->bindParam(':x1', $x1);
		$result_insert->bindParam(':y1', $y1);
		$result_insert->bindParam(':x2', $x2);
		$result_insert->bindParam(':y2', $y2);
		$result_insert->execute();
		$msg = "There is ";
		$style_color = "MediumSeaGreen;";

	}
	else{
		$msg = "ERROR REGION OVERLAP - Could not add the ";	
		$style_color = "Tomato;";
	}	
	foreach($result as $row){
		$p_number = $row['p_number'];	
		$p_name = $row['p_name'];
		$r_number = $row['r_number'];
		$s_name = $row['s_name'];
		$doctor_id = $row['doctor_id'];
	}

	echo("
		<p style=\"color:$style_color\">".$msg . "new clinical evidence on: </p><p id=\"newstyle\">Patient Name: $p_name 
			<br>Patient Number: $p_number <br>Element index: $e_index <br>Series Name: $s_name 
			(ID $s_id)<br>Study Request Number: $r_number <br> requested by: Doctor $doctor_id 
		</p>");
	echo("<br><a href = \"regions.php?ind=$e_index\">Go Back</a>");
?>
<?php include '../footer.php';?>
    </body>
</html>
