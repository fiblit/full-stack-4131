<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$eventNameErr = $startTimeErr = $endTimeErr = $locationErr = $dayErr = $imageURLErr = '';
$eventname = $startTime = $endTime= $location = $day = $imageURL = '';

function sanitizeInput($input) {
  //I am dubious of keeping stripslashes in there
  return htmlspecialchars(stripslashes(trim($input)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['Submit'])) {
    /* sanitize & validate  event name */
    if (empty($_POST['eventname'])) {
      $eventNameErr = "Please provide a value for Event Name";
    }
    else {
      $eventName = sanitizeInput($_POST['eventname']);
      if (!preg_match("/^.*$/",$eventName)) {
        $eventNameErr = "Invalid Event Name Format";
      }
    }

    /* sanitize & validate start time */
    if (empty($_POST['starttime'])) {
      $startTimeErr = "Please provide a value for Start Time";
    }
    else {
      $startTime = sanitizeInput($_POST['starttime']);
      if (!preg_match("/^([0|1][0-9]|2[0-3]):[0-5][0-9]$/",$startTime)) {
        $startTimeErr = "Invalid Start Time Format: please use 24-hour clock (00:00)";
      }
    }

    /* sanitize & validate end time */
    if (empty($_POST['endtime'])) {
      $endTimeErr = "Please provide a value for End Time";
    }
    else {
      $endTime = sanitizeInput($_POST['endtime']);
      if (!preg_match("/^([0|1][0-9]|2[0-3]):[0-5][0-9]$/",$endTime)) {
        $endTimeErr = "Invalid End Time Format: please use 24-hour clock (00:00)";
      }
    }

    /* sanitize & validate location */
    if (empty($_POST['location'])) {
      $locationErr = "Please provide a physical address for location";
    }
    else {
      $location = sanitizeInput($_POST['location']);
      if (!preg_match("/^.*$/",$location)) {
        $locationErr = "Invalid Location Format";
      }
    }

    /* sanitize & validate day-selector */
    if (empty($_POST['day'])) {
      $dayErr = "Please select a value for day";
    }
    else {
      $day = sanitizeInput($_POST['day']);
      if (!preg_match("/^(Mon|Tues|Wednes|Thurs|Fri)day$/",$day)) {
        $dayErr = "Invalid Day Format";
      }
    }

    /* sanitize & validate image url */
    if (empty($_POST['imageURL'])) {
      $imageURLErr = "Please provide a URL for the image";
    }
    else {
      $imageURL = sanitizeInput($_POST['imageURL']);
      if (!preg_match("/^.*$/",$imageURL)) {
        $imageURLErr = "Invalid URL Format";
      }
    }

    //auto redirect
    if ($locationErr === '' and $endTimeErr === '' and $startTimeErr === '' and $dayErr === '' and $eventNameErr === '') {
      $jsonFile = fopen("calendar.txt", "a+") or die("Failed to write event");
      $str = fread($jsonFile, filesize("calendar.txt"));
      fclose($jsonFile);
      if (strlen($str) >= 2) {
        $str[strlen($str)-2] = ',';
      }
      else {
        $str = "[\n";
      }
      $json = array(
        'day'=>substr($day,0,3),
        'startTime'=>$startTime,
        'endTime'=>$endTime,
        'eventName'=>$eventName,
        'location'=>$location, //sadly this can't include the room number
        'imageURL'=>$imageURL
      );
      $str = $str.json_encode($json)."]\n";
      $jsonFile = fopen("calendar.txt", "w") or die("Failed to write event");
      fwrite($jsonFile, $str);
      fclose($jsonFile);
      header('Location: calendar.php');
      die();
    }
  }
  elseif (isset($_POST["Clear"])) {
    $jsonFile = fopen("calendar.txt", "w") or die("Failed to write event");
    fwrite($jsonFile, '');
    fclose($jsonFile);
    header('Location: calendar.php');
    die();
  }
}

?>
<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <title>Calendar Input</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <header>
      <div class="container">
        <div id="advert">
          <button type = "button" class="ad-button" id="prev-button">
            <img src="Images/prev_blue.png" alt="prev_blue">
          </button>
          <a id = "advert-link" target="_blank">
            <img src="Images/spacer.gif" alt = "unloaded javascript" id ="advert-image" >
          </a>
          <button type = "button" class="ad-button" id="next-button">
            <img src="Images/next_blue.png" alt="next_blue">
          </button>
          <div class="container">
            <div id="bullets">
              <!-- I should try to get JS to write in the correct number of button-tabs dynamically -->
            </div>
          </div>
        </div>
      </div>

	  <script src="adverts.js"></script>

      <h1 class="center title">Dalton Hildreth's Calendar</h1>
      <nav>
        <div class="table-aligned-div">
          <ul>
            <li><button type="button" class = "nav-tab calendar-button" onClick="calendarButton()">Calendar</button></li>
            <li><button type="button" class = "nav-tab nav-current form-button" onClick="formButton()">Input</button></li>
          </ul>
        </div>
      </nav>

      <script src="nav.js"></script>

    </header>
    <!-- TODO: refactor to be more maintainable -->
    <div class="table-aligned-div">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
        <!-- TODO: add php for errors and restructure (if possible) the label tags -->
        <label>Event Name <span class="required">*</span> <span class="error"><?php echo $eventNameErr ?></span>
          <input class="form-input-col" name = "eventname" type="text" size="25" placeholder="Class"/>
        </label>
        <label>Start Time <span class="required">*</span> <span class="error"><?php echo $startTimeErr ?></span>
          <input class="form-input-col" name = "starttime" type="time" size="25"/>
        </label>
        <label>End Time <span class="required">*</span> <span class="error"><?php echo $endTimeErr ?></span>
          <input class="form-input-col" name = "endtime" type="time" size="25"/>
        </label>
        <label>Location <span class="required">*</span> <span class="error"><?php echo $locationErr ?></span>
          <input class="form-input-col" name = "location" type="text" size="25" placeholder="Hall"/>
        </label>
        <label>Day of the week <span class="required">*</span> <span class="error"><?php echo $dayErr ?></span>
          <select class="form-input-col" name = "day">
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
          </select>
        </label>
        <label>Image URL <span class="required">*</span> <span class="error"><?php echo $imageURLErr ?></span>
          <input class="form-input-col" name = "imageURL" type = "text" size = "25">
        </label>
        <div class="calendar-form-item">
		      <div class="calendar-form-col">
              <input type = "Submit" name="Clear" value="Clear"></input>
		      </div>
          <div class="calendar-form-col">
            <div class="calendar-form-rcol">
              <button class="form-input-col" type = "Submit" name="Submit">Submit</input>
            </div>
          </div>
        </div>
        <div class="calendar-form-item">
          <span class="required">* is required</span>
        </div>
      </form>
    </div>
    <div class = "tested-browsers">
      <p class="center"><cite>This page has been tested in Google Chrome and Internet Explorer. Typically at 1920x1080 or 1366x768.</cite></p>
    </div>
  </body>
</html>
