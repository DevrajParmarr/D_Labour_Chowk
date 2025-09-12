<?php
session_start();
include "../Shared/sqlconnection.php";

$sql_result=mysqli_query($conn, "select * from job_post");

   
   while($dbrow=mysqli_fetch_assoc($sql_result)){
   echo "<div class='cards'>
    <article class='card'>
        <div class='card__preview'>
          <img src='$dbrow[impath]'>   
        <div class='card__price'>
                 RS :$dbrow[salary] 
            </div>
        </div>
        <div class='card__content'>
            <h2 class='card__title'>$dbrow[jobTitle]</h2>
            <p class='card__address'>
                $dbrow[location]
            </p>
            <p class='card__description'> <br>
                $dbrow[detail]
            </p>
            <div class='card__bottom'>
                <div class='card__properties'>
                </div>
                <button class='card__btn' id='applyButton'>
                  Apply For Job</button>
            </div>
        </div>
    </article>
   
</div>";
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cards</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
    --primary: #111926;
    --white: #fff;
    --background: #F8F8FF;
    --gray: #D3D3D3;
    --text: #262626;
    --like: #FF3040;
}

body {
    display: flex;
    background-color: var(--background);
    justify-content: center;
    align-items: center;
    font-family: 'Mullish', sans-serif;
    /* height: 100vh; */
    padding: 1rem;
}

* {
    box-sizing: border-box;
    padding: 0;
    margin: 0;
}

.card {
    background: var(--white);
    border-radius: 1.5rem;
    box-shadow: rgba(0, 0, 0, 0.1) 0px 2px 6px;
    display: flex;
    width: 22rem;
    height: 29rem;
    flex-direction: column;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    border:1px solid rgb(111,112,211,1);
    margin:3px;
}

.card:hover {
    box-shadow: rgba(0, 0, 0, 0.1) 0px 6px 16px;
}

.card__preview {
    height: 12rem;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.card__preview img {
    width: 100%;
    object-fit: cover;
    transition: all 0.4s ease-out;
}

.card:hover .card__preview img {
    transform: scale(1.35);
}

.card__address {
    margin-top: 0.5rem;
}

.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 1rem;
}

.card__price {
    background-color: var(--white);
    color: var(--text);
    z-index: 1;
    position: absolute;
    bottom: 1.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    left: 1.25rem;
    font-weight: bold;
}

.card__content {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;

    color: var(--text);
}

.card__bottom {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card__description {
    margin-top: 0.5rem;
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 5;
}

.card__buttons {
    display: flex;
    gap: 0.5rem;
}

.card__btn {
    border: none;
    background-color:red;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
    border-radius: 6px;
    transition: background-color 0.4s ease, transform 0.4s ease; /* Transition effect */

}


.card__btn.clicked {
            background-color: rgb(252, 64, 12); /* Change to desired color */
            transform: scale(1.1); /* Slightly increase the size */
            border: 1px solid black;
            color:white;
        }

    </style>
</head>

<body>
    
<script>
        document.getElementById("applyButton").addEventListener("click", function() {
            let button = this;
            button.textContent = "Applied Successfully"; 
            button.classList.add("clicked");
            setTimeout(function() {
                button.classList.remove("clicked");
                button.textContent = "Apply for Job";
            }, 900); 
        });
    </script>
</body>
</html>