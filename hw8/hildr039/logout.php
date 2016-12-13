<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
session_unset();
session_destroy();
header('Location: login.php');
die();
?>
<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <title>Dalton Hildreth's Calendar</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <p>
      logging out...
    </p>
  </body>
</html>
