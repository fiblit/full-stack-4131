/* add the daily event extraction */
function extractTodaysEvents(){
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
        eventText = eventText.concat(todaysEvents[i].children[j].innerText.toString().concat(". "));
      }
      if (i < todaysEvents.length - 1) {
        eventText = eventText.concat("-/- ");
      }
    }
  }
  else {
    eventText = "NO EVENTS TODAY! STUDY! STUDY! STUDY!";
  }
  document.getElementsByClassName("days-events")[0].innerHTML = eventText;
}

/* Add the item hover functionality */
function addImageHover() {
  entries = document.getElementsByClassName("event-data")
  for (i = 0; i < entries.length; i++) {
    entries[i].getElementsByClassName("loc")[0].addEventListener("mouseover", function(self) {
      return function() {
        imgs = self.getElementsByClassName("imageURL");
        if (imgs.length > 0) {
          var imageURL = imgs[0].innerText;
          var xhttp = new XMLHttpRequest();
          xhttp.responseType = 'blob'; // immutable, raw data is expected. Basically a binary.
          var data = null;
          xhttp.onreadystatechange = function () {
              if (this.readyState == 4 && this.status == 200) {
                console.log(xhttp.getAllResponseHeaders());
                fr = new FileReader();//https://developer.mozilla.org/en-US/docs/Web/API/FileReader
                fr.onloadend = function () { //when done reading
                  newImg = document.createElement("IMG");
                  newImg.src = fr.result; // retrieve the file's contents
                  newImg.className = "table-preview-hover";
                  self.appendChild(newImg);
                }
                fr.readAsDataURL(xhttp.response); //also, make sure to read the blob right!
              }
          };
          xhttp.open("GET", imageURL, true);
          xhttp.send();
        }
      };
    }(entries[i]));

    entries[i].getElementsByClassName("loc")[0].addEventListener("mouseout", function(self) {
      return function() {
        imgs = self.getElementsByClassName("table-preview-hover");
        for (var i = 0; i < imgs.length; i++){
          self.removeChild(imgs[i]);
        }
      };
    }(entries[i]));
  }
}


window.onload = function () {
  extractTodaysEvents();
  addImageHover();
}
