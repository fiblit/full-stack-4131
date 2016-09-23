var todaysEvents = [];
var eventText = "";
var day = new Date().getDay();
var rows = document.getElementsByTagName("tr");
if (day > 0 && day < 6) {
  var i;
  for (i = 0; i < rows[day - 1].cells.length; i++){
    if (rows[day - 1].cells[i].className === "event-data") {
      todaysEvents.push(rows[day - 1].cells[i]);
    }
  }
}
else {
  todaysEvents = []; /* nothing on Saturday & Sunday */
}

if (todaysEvents.length > 0) {
  var i;
  for (i = 0; i < todaysEvents.length; i++) {
    var j;
    for (j = 0; j < todaysEvents[i].children.length; j++) {
      console.log(todaysEvents[i].children[j].innerText.toString());
      eventText = eventText.concat(todaysEvents[i].children[j].innerText.toString().concat(". "));
    }
    console.log(eventText);
    console.log(todaysEvents);
    if (i < todaysEvents.length - 1) {
      eventText = eventText.concat("-/- ");
    }
  }
}
else {
  eventText = "NO EVENTS TODAY! STUDY! STUDY! STUDY!";
}
document.getElementsByClassName("days-events")[0].innerHTML = eventText;