<?php

session_start();

// print_r($_POST);
// echo "<br>";
// print_r($_FILES);

$source_path=$_FILES["pdtimg"]["tmp_name"];
$file_name="../Shared/images/L_images".$_FILES["pdtimg"]['name'];

// <br>
// echo "temp file is in $source_path";
// <br>
// echo "File name= $file_name";

move_uploaded_file($source_path,$file_name);

include "../Shared/sqlconnection.php";
$workType=$_POST["workType"];
$salary=$_POST["salary"];
$experience=$_POST["experience"];
$location=$_POST["location"];
$city=$_POST["city"];

$query="insert into lab_post(user_ID ,workType,experience,salary,location,city,impath) values( {$_SESSION['user_id']} , '$workType','$experience', $salary,'$location', '$city' ,'$file_name'  )";

// echo "$query";


if (mysqli_query($conn, $query)) {
    sleep(2);
      echo "<h1>Successful Insertion</h1>";
      header('location:viewLP.php');
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>


<!-- mysqli_query($conn,$query ); -->


<!-- <form action="view.php" method="get">
    <button type="submit">View Product</button>
</form> -->
