@extends('welcome')

@section('content')
<style>
    #Gmap{
        width: 100%;
        height: 400px;
    }

</style>
<div class="row">
    <div class ="row">
        <div id="twitter-search-title" class="gm-style-mtc"
             style="direction: ltr; overflow: hidden; text-align: center; position: relative; color: rgb(0, 0, 0); font-family: Roboto,Arial,sans-serif; -moz-user-select: none; font-size: 11px; background-color: rgb(255, 255, 255); padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; background-clip: padding-box; box-shadow: 0px 1px 4px -1px rgba(0, 0, 0, 0.3); min-width: 21px; font-weight: 500;"
             >Please Search Location</div>
    </div>
    <div class="row">
        <div id="Gmap"></div>
        
        <div id="input-group" class="input-group col-lg-6">
            <input id="pac-input" class="form-control" name="location" type="text"
                placeholder="City Name">
                
            <span class="input-group-btn">
                <a href="{{ action('IndexController@history') }}" class="btn btn-info">History</a>
            </span>
        </div>
        
        
        <script>
            //Function to Initialize Map related Functions
            function initMap() {
                
                var map = new google.maps.Map(document.getElementById('Gmap'), {
                    center: {lat: -33.8688, lng: 151.2195},
                    zoom: 13
                });
                var title = document.getElementById('twitter-search-title');
                var infoWindow = new google.maps.InfoWindow();

                
                var input = /** @type {!HTMLInputElement} */(
                    document.getElementById('pac-input'));
                
                var markers = [];
                
                //Integrate controls to the Map canvas
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(title);
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(document.getElementById('input-group'));
                
                // bind input to google maps places autocomplete function.
                var autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', map);
                
                // listner for autocomplete place search input
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    //check if user selected a place
                    if (!place.geometry) {
                        window.alert("Please Wait for Autocomplete Suggestions.");
                        return;
                    }
                    // If the place has a geometry, then present it on a map.
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);  // Why 17? Because it looks good.
                    }
                    
                    //Change the Title according to the search
                    title.innerHTML = "Search Result for "+ place.name ;
                    
                    $.ajax({
                        method: "GET",
                        url: "{{ action('IndexController@locateTweets') }}",
                        data: { search: place.name, geoCode: place.geometry.location.lat() + "," + place.geometry.location.lng()}
                    })
                    .done(function( tweets ) {
                        deleteMarkers();
                        title.innerHTML += " ( Found " + tweets.length +" ) Tweets";
                        $.each(tweets,function(key){
                            addMarker(this);
                       });
                    });

                });
                        
                function addMarker(tweet){
                    var location = { lat: tweet.lat, lng: tweet.lng };
                    var marker = new google.maps.Marker({
                        position: location,
                        title: tweet.tweet + 'When:' + tweet.createdAt,
                        label: tweet.tweet + 'When:' + tweet.createdAt,
                        map: map,
                        icon: tweet.userPic
                    });
                    
                    marker.addListener('click', function() {
                        infoWindow.setContent(this.title);
                        infoWindow.open(map, this);
                    });
                    
                    markers.push(marker);
                }
                // Sets the map on all markers in the array.
                function setMapOnAll(map) {
                    for (var i = 0; i < markers.length; i++) {
                        markers[i].setMap(map);
                    }
                }
                
                function deleteMarkers() {
                    setMapOnAll(null);
                    markers = [];
                }
            }
            
    </script>

        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAXcK3uBAW2F7voEwEHvqZZjXjAwOe0p70&libraries=places&callback=initMap"
            async defer></script>

    </div>
</div>
    

@stop
