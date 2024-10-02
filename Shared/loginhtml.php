<?php require("login.php");?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    <?php 
    if($main_error != null){
        ?><style>.error{ display:block;}</style> <?php
    }?>
    

</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <form action="login.php" method="post" class="w-50  bg-info p-4">
            <h5 class="text-center">Login to D_Labour_Chowk</h5>
            <p class = "error" >
                <?php echo "$main_error" ;?>
            </p>
           
            <input required class="form-control mt-4" type="text" placeholder="Enter Mobile Number" name="mobile_no">
           
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

                <button class="btn btn-warning mt-3 w-25" onclick="openPopup()" id="login">Login</button>

                <div class="popup" id="popup">
                    <img src="tick.webp" alt="GreenTick">
                    <pre>LogIn Success!</pre>
                </div>

            </div>


    </div>


    <div class="text-end mt-3">
        <span class="text-start ">If you are new user </span>
        <a class="text-danger " href="sign_up.html">Sign Up here</a>
    </div>

    </form>
</div>


<script src="login.js"></script>
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

    </script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>