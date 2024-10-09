<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    

</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <form action="login.php" method="post" class="w-50  bg-info p-4">
            <h5 class="text-center">Login to D_Labour_Chowk</h5>

            <input required class="form-control mt-4" type="text" placeholder="Enter Mobile Number" name="mobile_no" id="contact">

            <div class="text-center mt-3">
                <div class="input-group mt-2">
                    <input required class="form-control" type="password" placeholder="Password" id="password"
                        name="password">
                    <p class="password-error"></p>
                    <button type="button" class="btn btn-outline-secondary" type="button" onclick="toggle()"
                        id="showbtn " style="background-color: aliceblue;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button class="btn btn-warning mt-3 w-25" id="login">Login</button>
                

                <div class="popup" id="popup">
                    <img src="tick.webp" alt="GreenTick">
                    <pre>LogIn Success!</pre>
                </div>
                <div class="popup" id="erpopup">
                   <img src="red cross.png" alt="img">
                   <pre>Check Details</pre>
                </div>

            </div>

            <div class="text-end mt-3">
                <span class="text-start ">If you are new user </span>
                <a class="text-danger " href="sign_up.html">Sign Up here</a>
            </div>


    </div>

    </form>
    </div>


    <!-- JAVA SCRIPT -->
    <script>
        function toggle() {
            if (state === 'show') {
                state = 'hide';
                pass2Obj.type = 'password';
                show.innerHTML = '<i class="bi bi-eye"></i>';
            } else if (state === 'hide') {
                state = 'show';
                pass2Obj.type = 'text';
                show.innerHTML = '<i class="bi bi-eye-slash"></i>';
            }
        }
        function openPopup() {
            console.log("function called");
            let Timeout;
             var element = document.getElementById("popup");
              if (element) { // Check if the element exists
                 element.classList.add("openPopup"); // Add the class
                 Timeout = setTimeout(closePopup, 3000);
             } else {
                console.error("Element not found!");
         }
        }
function closePopup() {
    popup.classList.remove("openPopup");
}
function wrong_credential(){
    let Timeout;
    var wrongpass = document.getElementById("password");
    var wrongcont = document.getElementById("contact");
    wrongpass.classList.add("htmlerror");
    wrongcont.classList.add("htmlerror");
    Timeout = setTimeout(close ,3000);
}
function close(){
    var wrongpass = document.getElementById("password");
    var wrongcont = document.getElementById("contact");
    
    wrongpass.classList.remove("htmlerror");
    wrongcont.classList.remove("htmlerror");
}
function eropenPopup(){
    let erpop = document.getElementById("erpopup");
    erpop.classList.add("eropenPopup");
    timeout = setTimeout(close1,3000);
}
function close1(){
    let erpop = document.getElementById("erpopup");
    erpop.classList.remove("eropenPopup");
}

    </script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>

<!-- PHP CODE STARTS -->

<?php
session_start();

$conn=new mysqli ("localhost","root","","d_labour", 3306);
$main_error = null;
$sql_result = mysqli_query($conn, "select * from user where mobile_no='$_POST[mobile_no]' and password='$_POST[password]' ");

$redirectUrl = 'login.php';

if($sql_result->num_rows==0){

    echo "<script type = 'text/javascript'>wrong_credential();</script>";
    echo "<script type ='text/javascript'>eropenPopup();</script>";
      
}else {
    $dbrow=mysqli_fetch_assoc($sql_result);
    
    $_SESSION["login_status"]=true;
    $_SESSION['user_id']=$dbrow['user_ID'];
    $_SESSION['user_name']=$dbrow['user_name'];
    $_SESSION['user_type']=$dbrow['user_type'];
    
    // if (isset($_SESSION['user_name'])) {
    //     echo "<h1>Hello {$_SESSION['user_name']}</h1>";
    //     // echo "<h1>Hello {$_SESSION['user_id'] }</h1>";
        
    
    // } else {
    //     echo "User name is not set.";
    // }
    
    if($dbrow["user_type"]=="User"){
        $redirectUrl = '../client_/availableLabour.php';

        echo "<script>console.log('user');</script>";
    echo "<script type = 'text/javascript'>openPopup()</script>";

    echo "<script>
    setTimeout(function() {
        window.location.href = '$redirectUrl';
    }, 3000); 
  </script>";
  
        

    //     echo "<script>alert('click ok to Start your journey');
    //  window.location.href = '$redirectUrl'; // Redirect after alert</script>";
    }
    // if($dbrow["user_type"]=="Labour"){
    //     header("location:../Labour/postL.php");
    // }
}

?>
