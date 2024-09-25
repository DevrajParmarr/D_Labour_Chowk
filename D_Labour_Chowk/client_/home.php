<?php
include "menu.html";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Labor</title>
</head>
<body>
    <h1>Search for Laborers</h1>
    <form action="search.php" method="get">
        <label for="workType">Work Type:</label>
        <input type="text" id="workType" name="workType" placeholder="e.g., Mason"><br><br>
        
        <label for="city">City:</label>
        <input type="text" id="city" name="city" placeholder="e.g., New York"><br><br>
        
        <input type="submit" value="Search">
    </form>
    <a href="index.php">Back to Home</a>
</body>
</html>
