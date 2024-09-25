<?php
 
print_r($_POST);

$connec = new mysqli("localhost", "root", "", "d_labour", 3306);

if ($connec->connect_error) {
    die("Connection failed: " . $connec->connect_error);
}

$name = $_POST['username']; // Use square brackets
$email=$_POST['email'];
$mobile=$_POST['mobile'];
$password = $_POST['password'];
$usertype = $_POST['usertype'];

$sql = "INSERT INTO user (user_name, email_id, mobile_no , password, user_type) VALUES ('$name','$email' , '$mobile' ,  '$password', '$usertype')";

if (mysqli_query($connec, $sql)) {
    echo "Successful Insertion";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($connec);
}

mysqli_close($connec);

?>
<div><form action="http://localhost/phpmyadmin/index.php?route=/sql&pos=0&db=d_labour&table=user" method="get">
    <button type="submit">Check your data in SQL</button>
</form>
</div>

