<?php

session_start();

sleep(3.1);
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


<!DOCTYPE html> <html lang="en"> 
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="creatLpost.css">
</head>
<body>


<div class="d-flex justify-content-center align-items-center vh-80">
<form class="w-50 bg-warning p-3" action="uploadL.php" method="post" enctype="multipart/form-data">
    
<select class="form-control mt-3" type="text" placeholder="Work Type / name ie Plumber , Carpenter " name="workType" required>
        <option value="">Select Work Type</option>
        <option value="electrician">Electrician</option>
        <option value="plumber">Plumber</option> 
        <option value="carpenter">Carpenter</option>
        <option value="painter">Painter</option>
        <option value="mason">Mason</option>
        <option value="painter">Other</option>
    </select>
           
<input class="form-control mt-2" type="number" placeholder="Salary you expected from client " name="salary"  required>
<input class="form-control mt-2" type="text" placeholder="Your Experience in this field " name="experience"  required>

    
<select class="form-control mt-3" type="text" placeholder="City in which you prefere to work" name="city"  required>
        <option value="">City </option>
        <option value="indore">Indore</option>
        <option value="bhopal">Bhopal</option> 
        <option value="carpenter">Ujjain</option>
        <option value="painter">Daiwas</option>
    </select>


<input class="form-control mt-3" type="text" placeholder="Area where client can find you" name="location"  required>

<input class="form-control mt-2" type="file" accept=" .jpg, .png, .jpeg" name="pdtimg"  required>
<div class="mt-3 text-center">
<button class="btn btn-success" onclick="openPopup()"> Creat Post</button>
</div>

</form>
</div>
<div class="popup" id="popup">
        <img src="tick.webp" alt="GreenTick">
        <pre>Post Created</pre> 
     </div>

<script src="creatLpost.js"></script>
</body>
</html>