/* Some of the following code was modified from this:
https://developers.google.com/maps/documentation/javascript/examples/geocoding-simple
*/

function initMap() {
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 15,
    center: {lat: 44.973, lng: -93.234}
  });

  var geocoder = new google.maps.Geocoder();
  document.getElementById('geocode').addEventListener('click', function() {
    geocodeAddress(geocoder, map);
  });


  var service = new google.maps.places.PlacesService(map);
  document.getElementById('find').addEventListener('click', function () {
    service.radarSearch({
      location: map.center,
      radius: document.getElementById('radius').value,
      type: ['restaurant']
    }, placeCallback);
  });

  /* the next two functions were modified from here:
  https://developers.google.com/maps/documentation/javascript/examples/place-search
  https://developers.google.com/maps/documentation/javascript/places#radar_search_requests
  */
  function placeCallback(results, status) {
    if (status === google.maps.places.PlacesServiceStatus.OK) {
      for (var i = 0; i < results.length; i++) {
        createRestaurant(results[i]);
      }
    }
  }

  function createRestaurant(place) {
    var placeLoc = place.geometry.location;
    var marker = new google.maps.Marker({
      map: map,
      position: place.geometry.location
    });

    var infowindow = new google.maps.InfoWindow();

    google.maps.event.addListener(marker, 'click', function() {
      service.getDetails(place, function(result, status) {
        if (status !== google.maps.places.PlacesServiceStatus.OK) {
          console.error(status);
          return;
        }
        infowindow.setContent("<p><b>"+ result.name +"</b></p>" + "<p>" + result.formatted_address + "</p>");
        infowindow.open(map, marker);
      });
    });
  }

  initMarkers(map);
}



function initMarkers(map) {
  data = getBuildingData();
  for (var i = 0; i < data.length; i++) {
    var infowindow = new google.maps.InfoWindow({
      content: data[i].info
    });

    var marker = new google.maps.Marker({
      animation: google.maps.Animation.BOUNCE,
      position: data[i].coord,
      map: map
    });
    marker.addListener('click', function(map, marker, infowindow) {
      return function() {
        infowindow.open(map, marker);
      }
    }(map, marker, infowindow));
  }
}

function getBuildingData() {
  var entries = document.getElementsByClassName("event-data")
  var data = [];
  for (var i = 0; i < entries.length; i++) {
    var event = entries[i].getElementsByClassName("event")[0].innerText;
    var loc = entries[i].getElementsByClassName("loc")[0].innerText;
    var info = "<p><i>" + event + "</i> - " + loc;

    var coordLatLng = entries[i].getElementsByClassName("coord")[0].innerText.split(" ");
    var coord = {lat: Number(coordLatLng[0]), lng: Number(coordLatLng[1])};

    var hasCoord = false;
    for (var c = 0; c < data.length; c++) {
      if (data[c].coord.lat === coord.lat && data[c].coord.lng === coord.lng) { //already has location
        var hasEvent = false;
        for (var j = 0; j < data[c].info.length; j++) {
          if (data[c].info[j] === info) { //already has event
            hasEvent = true;
            break;
          }
        }
        if (!hasEvent) {
          data[c].info.push(info);
        }
        hasCoord = true;
        break;
      }
    }
    if (!hasCoord) {
      var building = "<p><b>"+ entries[i].getElementsByClassName("building")[0].innerText + "</b></p>";
      data.push({coord: coord, info: [building]});
      data[data.length-1].info.push(info);
      //data.push({coord: coord, info: [info]});
    }
  }
  for (var d = 0; d < data.length; d++) {
    data[d].info = data[d].info.join().replace(/,/g,"");
  }

  return data;
}

function geocodeAddress(geocoder, resultsMap) {
  var address = document.getElementById('address').value;
  geocoder.geocode({'address': address}, function(results, status) {
    if (status === 'OK') {
      resultsMap.setCenter(results[0].geometry.location);
      var marker = new google.maps.Marker({
        map: resultsMap,
        position: results[0].geometry.location
      });

      console.log(results);
      var infowindow = new google.maps.InfoWindow({
        content: results[0].formatted_address
      });
      marker.addListener('click', function(map, marker, infowindow) {
        return function() {
          infowindow.open(map, marker);
        }
      }(map, marker, infowindow));
    } else {
      alert('Geocode was not successful for the following reason: ' + status);
    }
  });
}
