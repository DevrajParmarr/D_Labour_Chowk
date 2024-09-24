<?php

$post_id=$_GET["post_ID"];
include "../Shared/sqlconnection.php";


$status=mysqli_query($conn,"delete from job_post where post_id=$post_id");


if($status){
    header('location:view.php');
}else{
    echo "sql error ";
}


?>