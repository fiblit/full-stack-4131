<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function sortByStartTime($event1, $event2) {
  if ($event1['startTime']==$event2['startTime'])
    return 0;
  $event1Time = floatval(substr($event1['startTime'],0,2)) + floatval(substr($event1['startTime'],3,2))/60;
  $event2Time = floatval(substr($event2['startTime'],0,2)) + floatval(substr($event2['startTime'],3,2))/60;
  return ($event1Time<$event2Time?-1:1);
}
/* geocode*/
// function to geocode address, it will return false if unable to geocode address
//the following geocode function was modified from: https://www.codeofaninja.com/2014/06/google-maps-geocoding-example-php.html as provided in the hw7 description.
function geocode($address){
    $address = urlencode($address);
    $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";
    $resp_json = file_get_contents($url);
    $resp = json_decode($resp_json, true);
    if($resp['status']=='OK'){
        $lat = $resp['results'][0]['geometry']['location']['lat'];
        $long = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];
        if($lat and $long and $formatted_address){
            $data_arr = array();
            array_push($data_arr, $lat, $long, $formatted_address);
            return $data_arr;
        }
    }
    return false;
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
      <h1 class="center title">Dalton Hildreth's Calendar</h1>
      <nav>
        <div class="table-aligned-div">
          <ul>
            <li><button type="button" class = "nav-tab nav-current calendar" onClick="calendarButton()">Calendar</button></li>
            <li><button type="button" class = "nav-tab form-button" onClick="formButton()">Input</button></li>
          </ul>
        </div>
      </nav>

      <script src="nav.js"></script>

      <div class="clip">
        <div class="left hide-scrolltext"></div>
        <p class="days-events scroll-rtl table-aligned-div"><!-- This will be dynamically filled in --></p>
        <div class="right hide-scrolltext"></div>
      </div>
    </header>
    <table class="table-aligned-div">
      <tbody>
        <?php

        $jsonFile = fopen("calendar.txt","r");
        if (filesize("calendar.txt") > 0) {
          $json = fread($jsonFile,filesize("calendar.txt"));
        }
        else {
          $json = '';
        }
        $events = json_decode($json, true);//actually to be read&decode from calendar.txt
        $eventErr = '';
        if (count($events) === 0) {
          $eventErr = "Calendar has no events. Please use the input page to enter some events.";
        }

        if ($eventErr !== '') {
          echo "
          <div class = \"table-aligned-div\">
            <div style=\"background:white; padding: 5px;\"><span class=\"error\">$eventErr</span></div>
          </div>";
        }
        else {
          $days = array('Mon'=>array(),'Tue'=>array(),'Wed'=>array(),'Thu'=>array(),'Fri'=>array());
          foreach ($events as $event) {
            $days[$event['day']][] = $event;
          }
          foreach ($days as $day=>$todaysEvents){
            if (count($todaysEvents) > 0 ) {
              echo "<tr>\n";
              echo "<th><span class=\"dayofweek\">$day</span></th>\n";
              usort($todaysEvents, "sortByStartTime");

              foreach($todaysEvents as $event) {
                echo "<td class=\"event-data\">\n";
                $startTime = $event['startTime'];
                $endTime = $event['endTime'];
                echo "<p class=\"time\">$startTime-$endTime</p>\n";
                $eventName = $event['eventName'];
                echo "<p class=\"event\">$eventName</p>\n";
                $location = $event['location'];
                echo "<p class =\"loc\">$location</p>\n";
                $data = geocode($location);
                $lat = $data[0];
                $long = $data[1];
                $building = $data[2];
                echo "<span class=\"hidden-data coord\">$lat $long</span>";
                echo "<span class=\"hidden-data building\">$building</span>";
                $imageURL = $event['imageURL'];
                //echo "<img class=\"table-preview imageURL\" src=\"$imageURL\"/>";
                echo "<span class=\"hidden-data imageURL\">$imageURL</span>";
              }
            }
          }
        }
        ?>
      </tbody>
    </table>
    <div class="table-aligned-div">
      <form>
        <div class="calendar-form-item">
          <label class="calendar-form-col calendar-label">Address</label>
          <div class="calendar-form-col">
            <div class="calendar-form-rcol">
              <input id = "address" name = "address" type="text" size="25" placeholder="Keller Hall, MN" required/>
              <input id = "geocode" type = "button" value="Submit" /> <!-- might need to be a button not input -->
            </div>
          </div>
        </div>
      </form>
      <form>
        <div class="calendar-form-item">
          <label class="calendar-form-col calendar-label">Radius between 500 and 50,000</label>
          <div class="calendar-form-col">
            <div class ="calendar-form-rcol">
              <input id = "radius" name = "radius" type="number" placeholder="500" min="500" max="50000" required/>
              <input id = "find" type = "button" value="Find Nearby Restaurants" /> <!-- might need to be a button not input -->
            </div>
          </div>
        </div>
      </form>
    </div>
    <div id = "map" class = "map table-aligned-div" ></div>
    <div class = "tested-browsers table-aligned-div center">
      <cite>This page has been tested in Google Chrome and Mozilla Firefox. Typically at 1920x1080 or 1366x768.</cite>
    </div>

    <script src="map.js"></script>
    <script type="text/javascript" async defer src="https://maps.googleapis.com/maps/api/js?key=***REMOVED***&callback=initMap&libraries=places"></script>
    <script src="calendar.js"></script>
  </body>
</html>
