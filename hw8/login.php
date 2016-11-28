<?php
include_once "database_HW8F16.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$loginErr = $passwordErr = '';
$login = $password = '';

function sanitizeInput($input) {
  //I am dubious of keeping stripslashes in there
  return htmlspecialchars(stripslashes(trim($input)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  /* sanitize & validate  event name */
  if (empty($_POST['login'])) {
    $loginErr = "Please provide a value for the login field";
  }
  else {
    $login = sanitizeInput($_POST['login']);
    if (!preg_match("/^.*$/",$login)) {
      $loginErr = "Please enter a valid value for the login field";
    }
  }

  /* sanitize & validate start time */
  if (empty($_POST['password'])) {
    $passwordErr = "Please provide a value for the password field";
  }
  else {
    $password = sha1(sanitizeInput($_POST['password']));
    if (!preg_match("/^.*$/",$password)) {
      $passwordErr = "Please enter a valid value for the password field";
    }
  }
}

//query and redirect
if ($login !== '' and $password !== '') {
  $conn=new mysqli($db_servername,$db_username,$db_password,$db_name,$db_port);
  if ( $conn->connect_error ) {
    echo die("<h1>Could not connect to database</h></body></html>");
  }
  else {
    $query ="SELECT acc_password,acc_name FROM tbl_accounts WHERE acc_login='$login';";
    $result = $conn->query($query);
  }

  if (!isset($result) or !$result or $result->num_rows === 0) {
    $loginErr = "User does not exist. Please check the login details and try again.";
  }
  $arrResult = mysqli_fetch_row( $result );
  mysqli_data_seek($result, 0);

  if ($arrResult[0] !== $password) {
    $passwordErr = "Password is incorrect. Please check the password and try again.";
  }

  if ($loginErr === '' and $passwordErr === '' and $arrResult !== NULL) {
    $_SESSION['user'] = $arrResult[1];
    header('Location: calendar.php');
    die();
  }
}

?>
<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <title>Dalton Hildreth's Calendar</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <header>
      <h1 class="center title">Login Page</h1>
    </header>
    <div class="table-aligned-div">
      <p style="text-align:center;"> Please enter your user's login name and password. Both values are case sensitive.</p>
      <?php
      if ($loginErr != '') {
        echo "<p style=\"text-align:center;\">";
        echo "<span class=\"error\">$loginErr</span>";
        echo "</p>";
      }
      if ($passwordErr != '') {
        echo "<p style=\"text-align:center;\">";
        echo "<span class=\"error\">$passwordErr</span>";
        echo "</p>";
      }
      ?>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
        <!-- TODO: add php for errors and restructure (if possible) the label tags -->
        <label>Login <span class="error">
          <input class="form-input-col" name = "login" type="text" size="25"/>
        </label>
        <label>Password <span class="error">
          <input class="form-input-col" name = "password" type="password" size="25"/>
        </label>
        <div class="calendar-form-item">
		      <div class="calendar-form-col"></div>
          <div class="calendar-form-col">
            <div class="calendar-form-rcol">
              <input class="form-input-col" type = "Submit" name="Submit" value="Submit"></input>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class = "tested-browsers">
      <p class="center"><cite>This page has been tested in Google Chrome and Internet Explorer. Typically at 1920x1080 or 1366x768.</cite></p>
    </div>
  </body>
</html>
