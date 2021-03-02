<?php
session_start();
// Suppression des variables de session et de la session
$_SESSION = array();
session_destroy();

echo 'Déconnexion réussie';
echo "<br /><a href='index.php'>Reconnexion</a>";
 ?>
