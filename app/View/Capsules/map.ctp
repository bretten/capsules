<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript">
    // Define the namespace
    var mapView = {};

    // Will hold references to the Markers
    mapView.ownedMarkers = {};
    mapView.discoveredMarkers = {};

    mapView.getMarkers = function(latNE, lngNE, latSW, lngSW, callback) {
        $.ajax({
            type: 'POST',
            url: '/capsules/points/',
            data: {'data[latNE]': latNE, 'data[lngNE]': lngNE, 'data[latSW]': latSW, 'data[lngSW]': lngSW},
            success: function(data, textStatus, jqXHR) {
                if (data.length > 0) {
                    var data = $.parseJSON(data);
                    // Separate the Capsules and Discoveries
                    var capsules;
                    if (data.hasOwnProperty('capsules') && data.capsules.length > 0) {
                        capsules = data.capsules;
                    } else {
                        capsules = {};
                    }
                    var discoveries;
                    if (data.hasOwnProperty('discoveries') && data.discoveries.length > 0) {
                        discoveries = data.discoveries;
                    } else {
                        discoveries = {};
                    }
                    callback(capsules, discoveries);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                return false;
            }
        });
    }

    mapView.populateMarkers = function(map, capsules, discoveries) {
        // Remove all existing Markers
        $.each(mapView.ownedMarkers, function(id, marker) {
            marker.setMap(null);
        });
        $.each(mapView.discoveredMarkers, function(id, marker) {
            marker.setMap(null);
        });
        // Reinitialize Marker collections
        mapView.ownedMarkers = {};
        mapView.discoveredMarkers = {};

        var ownedVisible = $('#toggle_owned').prop('checked');
        var discoveredVisible = $('#toggle_discovered').prop('checked');
        $.each(capsules, function(index, value) {
            // Create the Marker
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(value.data.lat, value.data.lng),
                title: value.data.name,
                icon: 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                visible: ownedVisible,
                capsuleId: value.data.id
            });
            // Add the Marker to the Map
            marker.setMap(map);
            // Add the Marker to the collection of Markers
            mapView.ownedMarkers[value.data.id] = marker;
        });
        $.each(discoveries, function(index, value) {
            // Create the Marker
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(value.data.lat, value.data.lng),
                title: value.data.name,
                icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                visible: discoveredVisible,
                capsuleId: value.data.id
            });
            // Add the Marker to the Map
            marker.setMap(map);
            // Add the Marker to the collection of Markers
            mapView.discoveredMarkers[value.data.id] = marker;
        });
    }
</script>
<script type="text/javascript">
    // The Map options
    var mapOptions = {
        center: new google.maps.LatLng(47.618475, -122.365431),
        zoom: 10
    };
    // Will hold the Map
    var map;
    // Load the Map
    $(document).ready(function() {
        // Initialize the map
        map = new google.maps.Map(document.getElementById("map"), mapOptions);
        // Listeners
        google.maps.event.addListener(map, 'idle', function() {
            var bounds = map.getBounds();
            var latLngNE = bounds.getNorthEast();
            var latLngSW = bounds.getSouthWest();
            mapView.getMarkers(latLngNE.lat(), latLngNE.lng(), latLngSW.lat(), latLngSW.lng(), function(capsules, discoveries) {
                // Populate the map
                mapView.populateMarkers(map, capsules, discoveries);
            });
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Listener for toggling owned Capsules
        $('#toggle_owned, #toggle_discovered').change(function() {
            var markers;
            if ($(this).attr('id') === "toggle_owned") {
                markers = mapView.ownedMarkers;
            } else {
                markers = mapView.discoveredMarkers;
            }
            if ($(this).prop('checked') === true) {
                $.each(markers, function(id, marker) {
                    marker.setVisible(true);
                });
            } else {
                $.each(markers, function(id, marker) {
                    marker.setVisible(false);
                });
            }
        });
    });
</script>
<div id="map-controls">
    <div><input type="checkbox" id="toggle_owned" checked="true" />My Capsules</div>
    <div><input type="checkbox" id="toggle_discovered" checked="true" />My Discoveries</div>
</div>
<div id="map"></div>