<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript">
    // Define the namespace
    var mapView = {};

    // Will hold references to the Markers
    mapView.ownedMarkers = {};
    mapView.discoveredMarkers = {};
    mapView.undiscoveredMarkers = {};

    // Will hold the state of the Discovery Mode toggle
    mapView.discoveryModeOn;

    /**
     * Fetches stored Marker data
     */
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

    /**
     * Populates the GoogleMap with stored Markers
     */
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
            // Add the Marker InfoWindow event listener
            gmap.setupMarkerInfoWindow(marker, value.data, false /* isUndiscovered */);
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
            // Add the Marker InfoWindow event listener
            gmap.setupMarkerInfoWindow(marker, value.data, false /* isUndiscovered */);
            // Add the Marker to the Map
            marker.setMap(map);
            // Add the Marker to the collection of Markers
            mapView.discoveredMarkers[value.data.id] = marker;
        });
    }

    /**
     * Fetches undiscovered Capsule Markers
     */
    mapView.getUndiscoveredMarkers = function(lat, lng, callback) {
        $.ajax({
            type: 'POST',
            url: '/api/ping/',
            data: {'data[lat]': lat, 'data[lng]': lng},
            success: function(data, textStatus, jqXHR) {
                if (data.length > 0) {
                    var capsules = $.parseJSON(data);
                    callback(capsules);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                return false;
            }
        });
    }

    /**
     * Populates the GoogleMap with undiscovered Capsule Markers
     */
    mapView.populateUndiscoveredMarkers = function(map, capsules) {
        // Remove all existing Markers
        $.each(mapView.undiscoveredMarkers, function(id, marker) {
            marker.setMap(null);
        });
        // Reinitialize Marker collections
        mapView.undiscoveredMarkers = {};

        $.each(capsules, function(index, value) {
            // Create the Marker
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(value.data.lat, value.data.lng),
                title: value.data.name,
                icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                visible: mapView.discoveryModeOn,
                capsuleId: value.data.id
            });
            // Add the Marker InfoWindow event listener
            gmap.setupMarkerInfoWindow(marker, value.data, true /* isUndiscovered */);
            // Add the Marker to the Map
            marker.setMap(map);
            // Add the Marker to the collection of Markers
            mapView.undiscoveredMarkers[value.data.id] = marker;
        });
    }

    /**
     * Gets the Capsule list
     */
    mapView.updateCapsuleList = function(href) {
        var container;
        var uri;
        if (href === "#tab-pane-discoveries") {
            container = $('#tab-pane-discoveries');
            uri = "/discoveries/";
        } else {
            container = $('#tab-pane-capsules');
            uri = "/capsules/";
        }
        var loaded = (container.attr('data-loaded') != 0) ? true : false;
        if (!loaded) {
            $.ajax({
                type: 'GET',
                url: uri,
                success: function(data, textStatus, jqXHR) {
                    container.html(data);
                    // Flag that the data was loaded
                    container.attr('data-loaded', 1);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    container.html("The list could not be retrieved");
                }
            });
        }
    }
</script>
<script type="text/javascript">
    // Define the namespace
    var geoloc = {};

    // Geolocation options
    geoloc.options = {
        enableHighAccuracy: true
    };

    // The watch ID
    geoloc.watchId;

    // The current location
    geoloc.coordinates;

    /**
     * Callback for a successful geolocation update
     */
    geoloc.onPositionUpdate = function(position) {
        var coordinates = position.coords;
        geoloc.coordinates = position.coords;

        // Update the user's location circle
        if (!gmap.locationCircle.getVisible()) {
            gmap.locationCircle.setVisible(true);
        }
        gmap.locationCircle.setCenter(new google.maps.LatLng(coordinates.latitude, coordinates.longitude));

        // Get the latitude and longitude
        mapView.getUndiscoveredMarkers(coordinates.latitude, coordinates.longitude, function (capsules) {
            mapView.populateUndiscoveredMarkers(gmap.map, capsules);
        });
    }

    /**
     * Callback for handling a geolocation error
     */
    geoloc.onError = function(error) {
        if (error.code == error.PERMISSION_DENIED) {
            alert('demoed');
        } else if (error.code == error.POSITION_UNAVAILABLE) {
            alert('unavai;l');
        } else if (error.code == error.TIMEOUT) {
            alert('timeout');
        } else {
            alert('unknown error');
        }
    }
</script>
<script type="text/javascript">
    // The namespace
    var gmap = {};

    // The Map options
    gmap.mapOptions = {
        center: new google.maps.LatLng(47.618475, -122.365431),
        zoom: 10,
        styles: [
            {
                "featureType": "poi.attraction", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.business", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.government", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.medical", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.place_of_worship", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.school", "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "poi.sports_complex", "stylers": [{"visibility": "off"}]
            }
        ]
    };

    // Will hold the Map
    gmap.map;

    // The Marker InfoWindow
    gmap.markerInfoWindow = new google.maps.InfoWindow({
        content: ""
    });

    // The user's location circle
    gmap.locationCircle;

    // The zoom level for focusing on a single Capsule
    gmap.singleFocusZoom = 18;

    /**
     * Sets up the Marker InfoWindow including adding the listener and setting the content
     */
    gmap.setupMarkerInfoWindow = function(marker, capsule, isUndiscovered) {
        google.maps.event.addListener(marker, 'click', function() {
            // Set the content
            gmap.markerInfoWindow.setContent(
                '<h3>' + capsule.name + '</h3>'
                + '<div>'
                    + '<button type="button" id="capsule_list" class="btn btn-primary" data-toggle="modal" data-target="#modal-capsule-info" data-id="' + capsule.id + '">'
                        + (isUndiscovered ? 'Discover' : 'Open')
                    + '</button>'
                + '</div>'
            );
            // Open the info window
            gmap.markerInfoWindow.open(gmap.map, marker);
        });
    }

    // Load the Map
    $(document).ready(function() {
        // Initialize the map
        gmap.map = new google.maps.Map(document.getElementById("map"), gmap.mapOptions);
        // Listeners
        google.maps.event.addListener(gmap.map, 'idle', function() {
            var bounds = gmap.map.getBounds();
            var latLngNE = bounds.getNorthEast();
            var latLngSW = bounds.getSouthWest();
            mapView.getMarkers(latLngNE.lat(), latLngNE.lng(), latLngSW.lat(), latLngSW.lng(), function(capsules, discoveries) {
                // Populate the map
                mapView.populateMarkers(gmap.map, capsules, discoveries);
            });
        });
        // Create the user's location Circle
        gmap.locationCircle = new google.maps.Circle({
            strokeColor: '#A4C639',
            strokeOpacity: 0.4,
            strokeWeight: 1,
            fillColor: '#A4C639',
            fillOpacity: 0.4,
            map: gmap.map,
            visible: false,
            radius: <?php echo Configure::read('Capsule.Search.Radius'); ?> * 1609.34 // meters
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Disable Discovery Mode by default
        $('#toggle_discovery_mode').prop('checked', false);

        // Listener for toggling owned Capsules
        $('#toggle_owned, #toggle_discovered').change(function(e) {
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

        // Listener for toggling Discovery Mode
        $('#toggle_discovery_mode').change(function(e) {
            mapView.discoveryModeOn = $(this).prop('checked');
            if (mapView.discoveryModeOn == true) {
                if (navigator.geolocation) {
                    geoloc.watchId = navigator.geolocation.watchPosition(geoloc.onPositionUpdate, geoloc.onError, geoloc.options);
                } else {
                    alert('Geolocation not supported by the browser');
                }
            } else {
                // Stop watching for the geolocation
                navigator.geolocation.clearWatch(geoloc.watchId);
                // Remove the user's location circle
                gmap.locationCircle.setVisible(false);
                // Remove all existing Markers
                $.each(mapView.undiscoveredMarkers, function(id, marker) {
                    marker.setMap(null);
                });
                // Reinitialize Marker collections
                mapView.undiscoveredMarkers = {};
            }
        });

        // Listener for centering on the user's location
        $('#center-my-location').click(function(e) {
            if (mapView.discoveryModeOn == true) {
                gmap.map.setCenter(new google.maps.LatLng(geoloc.coordinates.latitude, geoloc.coordinates.longitude));
            }
        });
    });

    // Listener for going to a point on the map
    $(document).on('click', '.anchor-map-goto', function(e) {
        $('#modal-capsule-list').modal('hide');
        var lat = $(this).attr('data-lat');
        var lng = $(this).attr('data-lng');
        gmap.map.setCenter(new google.maps.LatLng(lat, lng));
        gmap.map.setZoom(gmap.singleFocusZoom);
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Hide other modals when opening a new one
        $('.modal').on('show.bs.modal', function (e) {
            $('.modal').modal('hide');
        });

        // Modal Capsule list content after shown listener
        $('#modal-capsule-list').on('shown.bs.modal', function(e) {
            mapView.updateCapsuleList($(this).find('.modal-body > .nav-tabs > li.active > a').attr('href'));
        });

        // Lisenter for modal Capsule list tabs
        $('#modal-capsule-list a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            mapView.updateCapsuleList($(e.target).attr('href'));
        });

        // Handler for GETing the content of the Capsule info modal
        $('#modal-capsule-info').on('shown.bs.modal', function(e) {
            var container = $(this).find('.modal-dialog > .modal-content > .modal-body');
            var requestData = {
                "data[id]": e.relatedTarget.dataset.id
            }
            if (typeof geoloc.coordinates !== 'undefined' && geoloc.coordinates.latitude !== 'undefined' && geoloc.coordinates.longitude !== 'undefined') {
                requestData["data[lat]"] = geoloc.coordinates.latitude;
                requestData["data[lng]"] = geoloc.coordinates.longitude;
            }
            $.ajax({
                type: 'POST',
                url: "/capsules/view/",
                data: requestData,
                success: function(data, textStatus, jqXHR) {
                    container.html(data);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    container.html("The data could not be retrieved");
                }
            });
        });
    });
</script>
<div id="modal-capsule-list" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-capsule-list" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label-capsule-list">Capsules</h4>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#tab-pane-capsules" role="tab" data-toggle="tab">My Capsules</a></li>
                    <li><a href="#tab-pane-discoveries" role="tab" data-toggle="tab">My Discoveries</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-pane-capsules" data-loaded="0"></div>
                    <div class="tab-pane" id="tab-pane-discoveries" data-loaded="0"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-capsule-info" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-capsule-info" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label-capsule-info">Capsule Info</h4>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
<div id="map-controls">
    <div><input type="checkbox" id="toggle_owned" checked="true" />My Capsules</div>
    <div><input type="checkbox" id="toggle_discovered" checked="true" />My Discoveries</div>
    <div><input type="checkbox" id="toggle_discovery_mode" />Discovery Mode <button type="button" id="center-my-location">Center on My Location</button></div>
    <div><button type="button" id="capsule_list" data-toggle="modal" data-target="#modal-capsule-list">Capsules</button></div>
</div>
<div id="map"></div>