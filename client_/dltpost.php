<?php
sleep(2);
$post_id=$_GET["post_ID"];
include "../Shared/sqlconnection.php";

$status=mysqli_query($conn,"delete from job_post where post_id=$post_id");
$rediractUrl = "view.php";

if($status){
    echo "<script type = 'text/javascript'>setTimeout(function(){
    window.location.href = '$rediractUrl';
    } , 0000)</script>";
    // header('location:view.php');
}else{
    echo "sql error ";
}
?>