/* data */
/* I generalized the code so that you can add as many adverts as you want to the adverts array
   the javascript will still function as should be expected. */
var adverts = [
  { "file": "Nerve.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14781/",
    "showTime": "Fri, Sep 30 7:00 PM",
    "duration": 7000
  },
  { "file": "secret-life-of-pets.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14786/",
    "showTime": "Fri, Oct 7 7:00 PM",
    "duration": 5000
  },
  { "file": "suicide-squad.jpg",
    "URL": "http://sua.umn.edu/events/calendar/event/14794/",
    "showTime": "Fri, Oct 14 8:00 PM",
    "duration": 3000
  }
];

/* helper functions */
var $ = function (id) {
  return document.getElementById(id);
}

//inits the correct number of bullets based on num of adverts
var bullets = []
function createBullets() {
  for (var i = 0; i < adverts.length; i++) {
    $("bullets").innerHTML += 
    '<button type = "button" class = "ad-button-tab ad-button">' +
      '<img src="Images/bullet_gray.png" alt="bullet-gray">' +
    '</button>';
    
    /*There is probably a better way of initially doing this by more intelligently using
      the DOM. This works, though, so I'm not going to try and change it for right now.*/
    //update the bullets array (normally I'd append/push, but that caused a strange bug)
    bullets = $("bullets").getElementsByTagName("button");
  }
}

var interval;
var currentAd;
//this function will set the advert
function setAdvert( i ) {
  clearInterval(interval);
  //prevent overflow, and to auto wrap everywhere
  i = ((i%adverts.length) + adverts.length) % adverts.length; //note, JS incorrectly handles negative modulo
  
  $("advert-image").src = "Images\\" + adverts[i].file;
  var txt = adverts[i].file.split(".")[0] + "\n" + adverts[i].showTime;
  $("advert-image").title = txt;
  $("advert-image").alt = txt;
  $("advert-link").href = adverts[i].URL;
  setCurrentBullet( i );
  interval = setInterval(function() { setAdvert(i+1); }, adverts[i].duration);
  currentAd = i;
}

//only used in setAdvert; sets the image of the current bullet
function setCurrentBullet( newBullet ) {
  //if there is something to unset
  if (currentAd != undefined) {
    //set the "old" bullet to non-current image/alt
    //(the "old" ad is on the currentAd index because we are transitioning to the new ad)
    var b = bullets[currentAd].getElementsByTagName("img")[0];
    b.src = "Images/bullet_gray.png";
    b.alt = "bullet_gray.png";
  }
  
  //set the new bullet to current image/alt
  var b = bullets[newBullet].getElementsByTagName("img")[0];
  b.src = "Images/bullet_blue.png";
  b.alt = "bullet_blue";
  
  //change event functionality of hovering
  setBulletEvents( newBullet )
}

//updates the hover and click functionality of the bullet tabs;
//takes in the "current" bullet (it has the correct image, but not the events yet)
//this is why I cannot just use the currentAd variable since it hasn't been updated yet; the advert is still in transition
function setBulletEvents( activeBullet ) {
  for (var i = 0; i < bullets.length; i++) {
    //hovering functionality only takes place on non-current bullets
    if (i != activeBullet) {
      bullets[i].onmouseover = 
        (function (i) { 
          return function () { 
            //the reason for the closure is because the i in the following line must equal
            //the i when it was assigned.
            var b = bullets[i].getElementsByTagName("img")[0];
            b.src = "Images/bullet_orange.png";
            b.alt = "bullet_orange"; 
          }        
        })(i);
      
      bullets[i].onmouseout = 
        (function (i) {
          return function () {
            //the reason for the closure is because the i in the following line must equal
            //the i when it was assigned.
            var b = bullets[i].getElementsByTagName("img")[0];
            b.src = "Images/bullet_gray.png";
            b.alt = "bullet_gray"; 
          }
        })(i);
        
      bullets[i].onclick = 
        (function (i) { 
          return function () {
            //the reason for the closure is because the i in the following line must equal
            //the i when it was assigned.
            setAdvert( i );
          }        
        })(i);
    }
    else { //remove hovering from current bullet
      bullets[i].onmouseover = undefined;
      bullets[i].onmouseout = undefined;
      bullets[i].onclick = undefined;
    }
  }
}

/* on-event functionality */

window.onload = function () {
  createBullets();
  setAdvert( 0 );
};

$("next-button").onclick = function () {
  setAdvert(currentAd + 1);
}
$("next-button").onmouseover = function () {
  var b = $("next-button").getElementsByTagName("img")[0];
  b.src = "Images/next_orange.png";
  b.alt = "next_orange";
}
$("next-button").onmouseout = function () {
  var b = $("next-button").getElementsByTagName("img")[0];
  b.src = "Images/next_blue.png";
  b.alt = "next_blue";
}

$("prev-button").onclick = function () {
  setAdvert(currentAd - 1);
}
$("prev-button").onmouseover = function () {
  var b = $("prev-button").getElementsByTagName("img")[0];
  b.src = "Images/prev_orange.png";
  b.alt = "prev_orange";
}
$("prev-button").onmouseout = function () {
  var b = $("prev-button").getElementsByTagName("img")[0];
  b.src = "Images/prev_blue.png";
  b.alt = "prev_blue";
}
