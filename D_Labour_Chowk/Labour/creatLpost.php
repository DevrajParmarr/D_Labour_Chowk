<?php

session_start();


if(!isset($_SESSION["login_status"])){
    echo "Login in skipped";
    die;
}

if($_SESSION["login_status"]==false){
    echo "Unauthorised Attempt";
    die;
}



include "menu.html";

echo "<h1 class='d-flex justify-content-center bg-white p-3 mt-3'>Hello {$_SESSION['user_name']}</h1>";

echo "<h1 class='d-flex justify-content-center '>If You want Work ,Let your potential Client Know </h1>";

?>


<!DOCTYPE html> <html lang="en"> <head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></head>
<body>


<div class="d-flex justify-content-center align-items-center vh-80">
<form class="w-50 bg-warning p-3" action="upload.php" method="post" enctype="multipart/form-data">
    
<input class="form-control mt-3" type="text" placeholder="Job Type/ Work name ie Plumber , Carpenter " name="jobTitle">

<!-- <label class="form-control mt-2">Select UserType</label>
            <select required class="form-control" name="jobtype" size="5">
                <option>Carpenter </option>
               <option>Electrician</option>
               <option>Plumber </option>
               <option>Supplyer</option>
               <option>Sweeper</option>
               <option>Painter</option>
               <option>Mason </option>
               <option>Labour</option>
            </select> -->
           
<input class="form-control mt-2" type="number" placeholder="Salary you expected from client " name="salary">
<input class="form-control mt-2" type="text" placeholder="Your Experience in this field " name="experience">
<input class="form-control mt-3" type="text" placeholder="Location:In which you prefered work" name="location">
<input class="form-control mt-2" type="file" accept=" .jpg, .png, .jpeg" name="pdtimg">
<div class="mt-3 text-center">
<button class="btn btn-success"> Creat Post</button>
</div>
</form>
</div>
</body>
</html>