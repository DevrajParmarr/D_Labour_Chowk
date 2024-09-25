<?php

$post_id=$_GET["l_post_ID"];
include "../Shared/sqlconnection.php";


$status=mysqli_query($conn,"delete from lab_post where l_post_ID=$post_id");


if($status){
    header('location:viewLP.php');
}else{
    echo "sql error ";
}


?>