 <?php

$methodType = $_SERVER["REQUEST_METHOD"];

// hier kijkt de if of we op de POST zitten
if(($methodType == "POST"))
{

    // met deze if kijk ik of allebij de wachtwoorden overeenkomen, als dat correct is wordt de code uitgevoerd
    if($_POST["wachtwoord1"] == $_POST["wachtwoord2"]){
try{
    // verbinding met de db
    $servernaam = "localhost";
    $gebruiker = "root";
    $wachtwoord = "root";
    $db = "dbsp2";   

     // nieuwe mysqli instantie maken
    $conn = new mysqli($servernaam, $gebruiker, $wachtwoord, $db);

    if($conn->connect_error)
{
    throw new exception($conn->connect_error);
}
    // de query om de account aan te maken
    $query = "INSERT INTO user(email, wachtwoord) VALUES (?,?)";

    $stmt = $conn->prepare($query);

    $postEmail = htmlspecialchars($_POST["email"]);
    $postWachtwoord = $_POST["wachtwoord1"];
    

    // hier beveilig ik de email en wachtwoord.
    $veiligEmail = htmlspecialchars($postEmail);
    $veiligWachtwoord = password_hash($postWachtwoord, PASSWORD_DEFAULT);

    $stmt->bind_param("ss", $veiligEmail, $veiligWachtwoord);

    // als de code niet werkt hoor ik een melding te zien
    if(!$stmt->execute())
    {
        throw new Exception($conn->error);
    }

    // ik zet deze bericht om te laten zien dat je regristratie gelukt is en dat na 2 seconden hij zal verdwijnen
    setcookie("bericht", "Regristratie gelukt", time()+2);

}catch(Exception $e){

    echo "oops: " . $e->getMessage();

}finally{

    if($stmt){
        $stmt->close();
    }

    if($conn){
        $conn->close();
    }
    // voor als de regristratie gelukt is wordt je terug naar de login pagina gestuurd om in te loggen
     header("location: login.php");
     exit;

}

}
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodCourt</title>
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body>
      <div id="header">
       <div>
            <img id="img" src="../IMG/cookie-png-transparent-images-background-23.png" alt="dauud">
        </div>
          <h1>Foodcourt</h1>
    </div>
    
  </div>


 <h1 id="h0">Maak je account aan</h1>
    <form action="regristreer.php" method="POST">

    <label>Email</label>
    <br>
    <input type="email" name="email">
    <br>
    <label>Wachtwoord</label>
    <br>
    <input type="password" name="wachtwoord1">
    <br>
    <label>Wachtwoord herhalen</label>
    <br>
    <input type="password" name="wachtwoord2">
    <br>
    <input type="submit" value="Maken!">

    <p>Al een account?</p>
    <a href="login.php">Inloggen!</a>


    </form>

   
</body>
</html>