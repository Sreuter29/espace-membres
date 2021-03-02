<?php
try
{
  $bdd = new PDO("mysql:host=localhost;dbname=minichat;charset=utf8", "root", "root", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

}
catch(Exception $e)
{
  die('Erreur : '.$e->getMessage());
}
$pseudoInfo = "Pseudo";
$emailInfo = "Adresse email";
$passwordInfo = "Mot de passe";
$erreur = null;
$deconnexion = null;

// Vérification de la validité des informations
if(isset($_POST['submit'])) {
  $mail = htmlspecialchars($_POST['mail']); // On rend inoffensives les balises HTML que le visiteur a pu rentrer
  $pseudo = htmlspecialchars($_POST['pseudo']);
  $pass = htmlspecialchars($_POST['pass']);
  $password = htmlspecialchars($_POST['password']);
  $pass_hache = password_hash($pass, PASSWORD_DEFAULT);
  $password_hache = password_hash($password, PASSWORD_DEFAULT);
  if(!empty($_POST['pseudo']) && !empty($_POST['pass']) && !empty($_POST['password']) && !empty($_POST['mail'])) {
    $reqpseudo = $bdd->prepare("SELECT * FROM membres WHERE pseudo = ?");
    $reqpseudo->execute([$pseudo]);
    $pseudoexist = $reqpseudo->rowcount();
    $reqpseudo->closeCursor();
    $reqpass = $bdd->prepare("SELECT * FROM membres WHERE pass = ?");
    $reqpass->execute([$pass_hache]);
    $passexist = $reqpass->rowcount();
    $reqpass->closeCursor();
    if($passexist === 0){
      if($pseudoexist === 0){
        if(preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $mail)){
          if($password === $pass){
            $crea = $bdd->prepare("INSERT INTO membres(pseudo, pass, email, date_inscription) VALUES(?, ?, ?, CURDATE())");
            $crea -> execute([$pseudo, $pass_hache, $mail]);
            $erreur = "Votre compte a été créé avec succés !";
          } else{
            $passInfo = "<strong>Les 2 mots de passe doivent être identiques !</stong>";
          }
        } else{
          $emailInfo = "<strong>L'adresse $mail n'est pas valide, recommençez !</strong>";
        }
      } else{
        $pseudoInfo ="<strong>Pseudo déja utilisé. Entrez un autre pseudo</strong>";
      }
    } else{
      $passwordInfo = '<strong>Ce mot de passe existe déjà. Entrez un autre mot de passe</strong>';
    }
  } else{
    $erreur = 'Tous les champs doivent être complétés !' ;
  }
}

//  Récupération de l'utilisateur et de son pass hashé
if(isset($_POST['submit'])) {
  $req = $bdd->prepare('SELECT id, pass FROM membres WHERE pseudo = :pseudo');
  $req->execute(array(
    'pseudo' => $pseudo));
    $resultat = $req->fetch();

    // Comparaison du pass envoyé via le formulaire avec la base
    $isPasswordCorrect = password_verify($pass, $pass_hache);

    if (!$resultat)
    {
      $erreur = 'Mauvais identifiant ou mot de passe !';
    }
    else
    {
      if ($isPasswordCorrect) {
        echo session_save_path();
        session_start();
        $_SESSION['id'] = $resultat['id'];
        $_SESSION['pseudo'] = $pseudo;
        $erreur = $_SESSION['pseudo'] .' '. "Votre compte a été créé avec succés ! Vous êtes connecté(e).";
        $deconnexion = true;
      }
      else {
        echo 'Mauvais identifiant ou mot de passe !';
      }
    }
  }
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="membres.css">
  </head>
  <style>
    body {background: rgba(1, 17, 64, 0.6);}
  </style>
  <body>
    <form action="index.php" method="post">
      <div><label for="pseudo"><?php echo $pseudoInfo;?></label> <input type="text" name="pseudo" id="pseudo" required>
      </div>
      <div><label for="password"><?php echo $passwordInfo; ?></label> <input type="password" name="password" id="password" required>
      </div>
      <div><label for="pass"><?php if(isset($passInfo)){echo $passInfo;} ?>Retapez votre mot de passe</label> <input type="password" name="pass" id="pass" required>
      </div>
      <div><label for="mail"><?php echo $emailInfo;?></label> <input type="email" name="mail" id="mail" required>
      </div>
      <button type="submit" name="submit">Inscription</button>
    </form>
    <?php
    if(!empty($erreur)){
      echo "<strong>$erreur</strong>";}
      if(!empty($deconnexion)){
        echo "<br /><a href='deconnexion.php'>Déconnexion</a>";
      }
      ?>
    </body>
    </html>
