<html>
    <head>
        <meta charset="utf-8">
        <title>SIBD Project</title>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
    </head>
    <body>
<?php include '../header.php';?>
<?php include '../database/db.php';?>
<?php

    $request_number = $_POST['request_number'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $doctor_id = $_POST['doctor_id'];
    $manufacturer = $_POST['manufacturer'];
    $serial_number = $_POST['serial_number'];
    $series_name = $_POST['series_name'];
    $series_description = $_POST['series_description'];

    /* If series already exists */
    $sql = "SELECT series_id FROM Series 
            WHERE series_id >= 
            ALL(SELECT series_id FROM Series);";
    $result = $connection->query($sql);
    if ($result == FALSE) {
        $info = $connection->errorInfo();
        echo("<p>ERROR: {$info[0]} {$info[1]} {$info[2]}</p>");
        exit(0);
    }
    $nrows = $result->rowCount();
    if($nrows == 0) {
        $series_id = 1;
    }
    else {
        $last_series_id = $result->fetchColumn(0);
        $series_id = $last_series_id + 1;
    }
    $url = "https://web.ist.utl.pt/".$user."/series/".$series_id; 

    /* Try insert in database (to avoid SQL Injetion)*/
    try {
        $sql_study = "INSERT INTO Study values (:request_number, :description, :s_date, :doctor_id, :serial_number, :manufacturer);";
        $result_study = $connection->prepare($sql_study);
        $sql_series = "INSERT INTO Series values (:series_id, :series_name, :base_url, :request_number, :series_description);";
        $result_series = $connection->prepare($sql_series);

        $connection->beginTransaction();

        $result_study->bindParam(':request_number',$request_number);
        $result_study->bindParam(':description',$description);
        $result_study->bindParam(':s_date',$date);
        $result_study->bindParam(':doctor_id',$doctor_id);
        $result_study->bindParam(':serial_number',$serial_number);
        $result_study->bindParam(':manufacturer',$manufacturer);
        $result_study->execute();

        $result_series->bindParam(':series_id',$series_id);
        $result_series->bindParam(':series_name', $series_name);
        $result_series->bindParam(':base_url',$url);
        $result_series->bindParam(':request_number',$request_number);
        $result_series->bindParam(':series_description',$description); 
        $result_series->execute();

        $connection->commit();
    }
    catch(PDOException $err) {
        $connection->rollBack();
        echo("<p> A new study was NOT created! ERROR: $err.</p>");
        echo("<a href='create_study.php'>Go back</a>");
        exit(0);
    }

    if ($result_study == FALSE || $result_series == FALSE) {
        $connection->rollBack();
        $info = $connection->errorInfo();
        echo("<p>ERROR: {$info[0]} {$info[1]} {$info[2]}</p>");
        exit(0);
    }       

    echo("<p> A new study was created! </p>");
    echo("<a href='create_study.php'>Go back</a>");

    $connection = null;
?>
    <?php include '../footer.php';?>
    </body>
</html>
