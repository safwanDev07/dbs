<?php
session_start();

// met deze stuk code kijkt hij of je ingelogd bent, zo ja dan krijg je een kleine bericht die zegt dat je ingelogd bent
if(isset($_SESSION["login"])){
  echo "<h3 id='loginh3'>Je bent ingelogd!</h3>";}
else{
  // Hier zeg ik als je niet ingelogd bent wordt je teruggestuurd naar de log in pagina/
 header("Location: login.php");
 exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Court</title>
    <link rel="stylesheet" href="./CSS/style.css">

</head>
<body>
    <div id="hometext">
     <div><p>Welkom bij Food Court! Ontdek lekkere gerechten en deel je favoriete gerechten met andere.</p></div>
    </div>
</body>
</html>

