<?php
include_once "database_HW8F16.php";

//for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//for the Model

//for security
session_start();
if(! isset($_SESSION["user"]) ) {
  header('Location: login.php');
  die();
}

class Model {
    public $text;
    public $errorMSG;
    public $errorTYPE;
    public $curusr;
    public $editing;
    public $conn;

    public function __construct() {
      global $db_servername;
      global $db_username;
      global $db_password;
      global $db_name;
      global $db_port;
      $this->conn=new mysqli($db_servername,$db_username,$db_password,$db_name,$db_port);
      $this->text = 'Hello world!';
      if ( $this->conn->connect_error ) {
          $this->errorMSG = ("Could not connect to the database.");
          $this->errorTYPE = "FATAL";
      }

      $this->curusr = $_SESSION["user"];
      if (isset($_SESSION["edit"])) {
        $this->editing = $_SESSION["edit"];
      }
      else {
        $this->editing = NULL;
      }

      if (isset($_SESSION["msg"])) {
        $this->errorMSG = $_SESSION["msg"];
      }
      else {
        $this->errorMSG = NULL;
      }
    }

    private function sanitizeInput($input) {
      //I am dubious of keeping stripslashes in there
      return htmlspecialchars(stripslashes(trim($input)));
    }

    public function all_user_info() {
      $query = "SELECT * FROM tbl_accounts ORDER BY acc_login;";
      $result = $this->conn->query($query);
      $arrResult = mysqli_fetch_all($result,MYSQLI_ASSOC);
      return $arrResult;
    }

    public function login_user_info() {
      $query = "SELECT acc_login FROM tbl_accounts ORDER BY acc_login;";
      $result = $this->conn->query($query);
      $arrResult = mysqli_fetch_all($result,MYSQLI_ASSOC);
      return $arrResult;
    }

    private function insert_user($name, $login, $password) {
      //this next line needs to be protected from sql-insertion at some point.
      $query = "INSERT INTO tbl_accounts (acc_name, acc_login, acc_password) VALUES ('".$name."', '".$login."', '".$password."');";
      $result = $this->conn->query($query);
      return 0;
    }

    public function delete_user($login) {
      $query = 'DELETE FROM tbl_accounts WHERE acc_login=\''.$login.'\';';
      $this->conn->query($query);
      $_SESSION['msg'] = "account deleted successfuly";
      return 0;
    }

    public function update_user($name, $login, $password) {
      $query = 'UPDATE tbl_accounts SET acc_name=\''.$name.'\', acc_login=\''.$login.'\', acc_password=\''.$password.'\' WHERE acc_login=\''.$this->editing.'\';';
      $this->conn->query($query);
      return 0;
    }

    public function edit($edit) {
      $_SESSION['edit'] = $edit;
      header('Location: admin.php');
      die();
    }

    public function do_update($name_IN, $login_IN, $password_IN) {
      //disinfectant,
      $name = $this->sanitizeInput($name_IN);
      $login = $this->sanitizeInput($login_IN);
      $password = sha1($this->sanitizeInput($password_IN));

      //bleach,
      if (0 != $this->validate_login($login)) {
        if ($login !== $this->editing) {
          return 1;
        }
      }
      if (0 != $this->validate_password($password)) {
        return 1;
      }
      if (0 != $this->validate_name($name)) {
        return 1;
      }

      //and a little rubbing alcohol
      $this->update_user($name, $login, $password);

      $_SESSION['msg']='Account updated succesfully';
      $this->edit(NULL);

      //will do the job
      header('Location: admin.php');
      die();
    }

    public function do_add_user($name_IN, $login_IN, $password_IN) {
      //disinfectant,
      $name = $this->sanitizeInput($name_IN);
      $login = $this->sanitizeInput($login_IN);
      $password = sha1($this->sanitizeInput($password_IN));

      //bleach,
      if (0 != $this->validate_login($name)) {
        return 1;
      }
      if (0 != $this->validate_password($password)) {
        return 1;
      }
      if (0 != $this->validate_name($name)) {
        return 1;
      }
      //and a little rubbing alcohol
      $this->insert_user($name, $login, $password);

      $_SESSION['msg']='Account added succesfully';

      //will do the job
      header('Location: admin.php');
      die();
    }

    private function validate_name($name) {
      if (!preg_match("/^.+$/",$name)) {
        $this->errorMSG = "Please enter a non-empty value for the name field";
        $this->errorTYPE = "MINOR";
        return 1;
      }
      return 0;
    }

    private function validate_login($login) {
      if (!preg_match("/^.+$/",$login)) {
        $this->errorMSG = "Please enter a non-empty value for the login field";
        $this->errorTYPE = "MINOR";
        return 1;
      }
      //check it is unique
      if ($this->specialexists($login, $this->login_user_info())) {
        $this->errorMSG = "That login is already taken by another account. Please choose another.";
        $this->errorTYPE = "MINOR";
        return 1;
      }
      return 0;
    }

    private function specialexists($needle, $haystack) {
      foreach ($haystack as $nedle) {
        if ($needle === $nedle['acc_login']) {
          return true;
        }
      }
      return false;
    }

    private function validate_password($password) {
      if (!preg_match("/^.+$/",$password)) {
        $this->errorMSG = "Please enter a non-empty value for the password field";
        $this->errorTYPE = "MINOR";
        return 1;
      }
      return 0;
    }
    /*
      $query ="SELECT acc_password,acc_name FROM tbl_accounts WHERE acc_login='$login';";
      $result = $conn->query($query);

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
    */
}

class View {
    private $model;
    private $controller;

    public function __construct(Controller $controller, Model $model) {
        $this->controller = $controller;
        $this->model = $model;
    }

    private function out_header() {
      return '
      <header>
        <h1 class="center title">Dalton Hildreth\'s Calendar</h1>
        <div class="welcome">
          <p>Welcome '.$this->model->curusr.'!</p>
          <button type="button" onClick="logout()">Log out</button>
        </div>
        <nav>
          <div class="table-aligned-div">
            <ul>
              <li><button type="button" class = "nav-tab calendar" onClick="calendarButton()">Calendar</button></li>
              <li><button type="button" class = "nav-tab form-button" onClick="formButton()">Input</button></li>
              <li><button type="button" class = "nav-tab nav-current form-button" onClick="adminButton()">Admin</button></li>
              </ul>
          </div>
        </nav>
        <script src="nav.js"></script>
      </header>';
    }

    private function out_user_table() {
      $userlist = $this->model->all_user_info();
      $myhtml = '
      <form class="table-aligned-div" method="post" action='.htmlspecialchars($_SERVER["PHP_SELF"]).'>
      <h1 style="">List of Users</h1>';
      if ($this->model->errorMSG != NULL) {
        $myhtml .= '<p class="error">'.$this->model->errorMSG.'</p>';
      }
      $myhtml .= '<table class="tableusers">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Login</th>
        <th>Password</th>
        <th>Actions</th>
      </tr>';
      for ($user = 0; $user < count($userlist); $user++) {
        $myhtml .='
        <tr>
          <td class="userid">'.$userlist[$user]['acc_id'].'</td>
          <td class="username">'.$this->out_username($userlist[$user]).'</td>
          <td class="userlogin">'.$this->out_userlogin($userlist[$user]).'</td>
          <td class="userpassword">'.$this->out_userpassword($userlist[$user]).'</td>
          <td class="action">'.$this->out_userbuttons($userlist[$user]).'</td>
        </tr>';
      }
      $myhtml .='</table></form>';

      return $myhtml;
    }

    private function out_username($user) {
      if ($this->model->editing == $user['acc_login']) {
        return '
          <input name = "name" type="text" placeholder="'.$user['acc_name'].'"/>';
      }
      else {
        return $user['acc_name'];
      }
    }

    private function out_userlogin($user) {
      if ($this->model->editing == $user['acc_login']) {
        return '
          <input name = "login" type="text" placeholder="'.$user['acc_login'].'"/>';
      }
      else {
        return $user['acc_login'];
      }
    }

    private function out_userpassword($user) {
      if ($this->model->editing == $user['acc_login']) {
        return '
          <input name = "password" type="password"/>';
      }
      else {
        return '';//$user['acc_password'];
      }
    }

    private function out_userbuttons($user) {
      if ($this->model->editing == $user['acc_login']) {
        return '<button type="submit" name="Update" value = "'.$user['acc_login'].'">Update</button>
        <button type="submit" name="Cancel" value = "'.$user['acc_login'].'">Cancel</button>';
      }
      else {
        return//I am literally a genius for these next two lines. YEAH!
        '<button type="submit" name="Edit" value = "'.$user['acc_login'].'">Edit</button>
        <button type="submit" name="Delete" value = "'.$user['acc_login'].'">Delete</button>';
      }
    }

    private function out_add_new_user() {
      return '<form class="table-aligned-div" method="post" action='.htmlspecialchars($_SERVER["PHP_SELF"]).'>
      <h1 style="">Add new user</h1>
      <label>Name
        <input class="form-input-col" name = "name" type="text" size="25"/>
      </label>
      <label>Login
        <input class="form-input-col" name = "login" type="text" size="25"/>
      </label>
      <label>Password
        <input class="form-input-col" name = "password" type="password" size="25"/>
      </label>
      <div class="calendar-form-item">
        <div class="calendar-form-col"></div>
        <div class="calendar-form-col">
          <div class="calendar-form-rcol">
            <input class="form-input-col" type = "Submit" name="AddUser" value="AddUser"></input>
          </div>
        </div>
      </div>
      </form>
      ';
    }

    public function output_body() {
      return $this->out_header()
      .$this->out_user_table()
      .$this->out_add_new_user();
    }
}

class Controller {
    private $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    public function textClicked() {
        $this->model->text = 'Text Updated';
    }

    /* this is just to identify what input came in. Not "real" logic. */
    public function process_request() {
      unset($_SESSION['msg']);
      if (isset($_POST['AddUser'])) {
        $this->model->do_add_user($_POST['name'], $_POST['login'], $_POST['password']);
      }
      elseif (isset($_POST['Edit'])) {
        $this->model->edit($_POST['Edit']);
      }
      elseif (isset($_POST['Delete'])) {
        $this->model->delete_user($_POST['Delete']);//genius, I tell you.
        header('Location: admin.php');
        die();
      }
      elseif (isset($_POST['Update'])) {
        $this->model->do_update($_POST['name'], $_POST['login'], $_POST['password']);
      }
      elseif (isset($_POST['Cancel'])) {
        $this->model->edit(NULL);
      }
      else {
        $this->model->errorMSG = "I'm sorry, I don't know how to do that. Unkown control request.";
        $this->model->errorTYPE = "MINOR";
      }
    }
}

if (!isset($model)) {
  $model = new Model();
  $controller = new Controller($model);
  $view = new View($controller, $model);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") $controller->process_request();
if (isset($_GET['action'])) $controller->{$_GET['action']}();
?>

<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <title>Dalton Hildreth's Calendar</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
      <?php echo $view->output_body(); ?>
      <!-- TBI: Table of users (ID, Name, Login, New Pasword, Action) likely grabbed from Model
      Edit button under action activates edit mode: can UPDATE username, login, and password.
        Edit Mode changes Action Buttons to Update and Cancel.
        Cancel discards changes and exits Edit mode.
        Update validates input, checks for unique login, and uses SHA1 on the password and stores it (via update) in the Model/DB.
          if Update is unsuccessful, error message should print and return to edit mode
          if successful, exit edit mode, display user list, and display message that user has been updated.
      The admin page also hass section to ADD NEW USER.
        if you press add user button, the CONTROLLER should validate the input, check to ensure login uniqueness,
        use SHA1 to hash the password and add the new user information to the DB/Model.
        It should siplay a message if the user is successfully added, or an error occurs.
      Delete button  should delete the user from the Model.

      NO TWO USERS CAN HAVE THE SAME LOGIN

      Go by (easy) remaining points:
      5+25+35+35+10+10+10+15+10+25 -30(late) = 170/200 Likely
      -->
  </body>
</html>
