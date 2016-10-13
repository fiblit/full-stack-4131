/* Some of the following code was modified from this: 
https://developers.google.com/maps/documentation/javascript/examples/geocoding-simple 
*/
function initMap() {
  console.log("hello???")
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 15,
    center: {lat: 44.974, lng: -93.234}
  });
  console.log(map)
  var geocoder = new google.maps.Geocoder();

  document.getElementById('geocode').addEventListener('click', function() {
    geocodeAddress(geocoder, map);
  });
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
    } else {
      alert('Geocode was not successful for the following reason: ' + status);
    }
  });
}
/* end of modified code code */