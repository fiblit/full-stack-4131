/* data */
var adverts = [
  { "file": "Nerve.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14781/",
    "showTime": "Fri, Sep 30, 7:00 PM",
    "duration": 7000
  },
  { "file": "secret-life-of-pets.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14786/",
    "showTime": "Fri, Oct 7, 7:00 PM",
    "duration": 5000
  },
  { "file": "suicide-squad.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14794/",
    "showTime": "Fri, Oct 14, 8:00 PM",
    "duration": 3000
  }
];

/* helper functions */
var $ = function (id) {
  return document.getElementById(id);
}

var interval;
var currentAd;
//this function will set the advert
function setAdvert( i ) {
  clearInterval(interval);
  i %= adverts.length; //This is to prevent overflow, and to auto wrap everywhere
  var img = document.getElementById("advert-image");
  var lnk = document.getElementById("advert-link");
  img.src = "Images\\" + adverts[i].file;
  var text = adverts[i].file.split(".")[0] + "\n" + adverts[i].showTime;
  img.title = text;
  img.alt = text;
  lnk.href = adverts[i].URL;
  interval = setInterval(function() { setAdvert(i+1); }, adverts[i].duration);
  currentAd = i;
}

/* on-event functionality */

window.onload = setAdvert( 0 );

function nextAdvert() {
  setAdvert(currentAd + 1);
  //do button magic
}

function prevAdvert() {
  setAdvert(currentAd - 1);
  //do button magic
}