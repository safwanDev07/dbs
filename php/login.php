<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foodcourt</title>
     <link rel="stylesheet" href="../CSS/style.css">
     
    
</head>
<body>
     <div id="header">
          <div>
            <img id="img" src="../IMG/cookie-png-transparent-images-background-23.png" alt="dauud">
        </div>
      <h1>Foodcourt</h1>
    </div>
    
    <h1 id="h0">Inloggen</h1>
    <form action="login.php" method="POST">

    <label>Email</label>
    <br>
    <input type="email" name="email">
    <br>
    <label>Wachtwoord</label>
    <br>
    <input type="password" name="wachtwoord">
    <br>
    <input type="submit" value="Login">

    <p>Nog geen account?</p>
    <a href="regristreer.php">Account aanmaken!</a>

    


    </form>
    <?php

$methodType = $_SERVER["REQUEST_METHOD"];

// hier kijkt de if of we op de POST zitten.
if(($methodType == "POST") && (isset($_POST["email"])))
{
try{

    // Verbind met database als app user (beperkte rechten)
    $servernaam = "localhost";
    $gebruiker = "app_user";  // Gebruikt nu de app_user met beperkte rechten
    $wachtwoord = "user_password_secure";
    $db = "dbsp2";

    $conn = new mysqli($servernaam, $gebruiker, $wachtwoord, $db);

    if($conn->connect_error)
{
    throw new exception($conn->connect_error);
}

    //hier mee haal ik data uit de db om te kijken of de ingevoerde data ook overeen komt met de data uit de db zodat de gebruiker kan inloggen
    $query = "SELECT id, email, wachtwoord FROM user WHERE email = ?";

    $stmt = $conn->prepare($query);

    $postEmail = $_POST["email"];
    $postWachtwoord = $_POST["wachtwoord"];

    $veiligEmail = htmlspecialchars($postEmail);

    $stmt->bind_param("s", $veiligEmail);  

    if (!$stmt->execute()){
        throw new exception($conn->error);
    }

    $stmt->bind_result($id, $email, $wachtwoord);

    $databaseEmail = "<error>";
    $databaseWachtwoord = "<error>";



    while ($stmt->fetch()){
        $databaseEmail = $email;
        $databaseWachtwoord = $wachtwoord;
    }

        
    // hiermee zeg ik, als er geen email is gevonden in de database, krijg je een error
    if ($databaseEmail == "<error>")
    {
        echo "";
        // hiermee zeg ik als de wachtwoorden niet overeenkomen, krijg je te zien dat je een verkeerde wachtwoord hebt geschreven
    } else if (!password_verify($postWachtwoord,$databaseWachtwoord)){

        echo "Foute Wachtwoord!";
    }
    else{
        $_SESSION["login"] = $databaseEmail;
        header("Location:index.php");
     
        

    }

}catch(Exception $e){
    echo "oops: " . $e->getMessage();

}finally{
  
    if($stmt){
        $stmt->close();
    }

    if($conn){
        $conn->close();
    }

}

}

?>

</body>
</html>