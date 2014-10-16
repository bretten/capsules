<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript">
    // Define the namespace
    var mapView = {};

    // "Contstants" to differentiate Capsule types
    mapView.CAPSULE_OWNERSHIP = 0;
    mapView.CAPSULE_DISCOVERY = 1;
    mapView.CAPSULE_UNDISCOVERED = 2;

    // Icon images
    mapView.ICON_CAPSULE_OWNERSHIP = "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
    mapView.ICON_CAPSULE_DISCOVERY = "https://maps.google.com/mapfiles/ms/icons/blue-dot.png";
    mapView.ICON_CAPSULE_UNDISCOVERED = "https://maps.google.com/mapfiles/ms/icons/red-dot.png";
    mapView.ICON_CAPSULE_NEW = "https://maps.google.com/mapfiles/ms/icons/green-dot.png";

    // Will hold references to the Markers
    mapView.ownedMarkers = {};
    mapView.discoveredMarkers = {};
    mapView.undiscoveredMarkers = {};

    // Will hold a reference to the new Capsule Marker
    mapView.newCapsuleMarker = new google.maps.Marker({
        title: "New Capsule",
        icon: mapView.ICON_CAPSULE_NEW,
        draggable: true,
        animation: google.maps.Animation.DROP
    });

    // Will hold the last viewed paginated lists to maintain the query parameters on a refresh
    mapView.paginationUri = {
        capsules: "/capsules/",
        discoveries: "/discoveries/"
    }

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
     * Populates the GoogleMap with stored Markers
     */
    mapView.populateMarkers = function(map, collection, capsules, type, visible) {
        // Determine type specific properties
        var icon;
        if (type === mapView.CAPSULE_OWNERSHIP) {
            icon = mapView.ICON_CAPSULE_OWNERSHIP;
        } else if (type === mapView.CAPSULE_DISCOVERY) {
            icon = mapView.ICON_CAPSULE_DISCOVERY;
        } else {
            icon = mapView.ICON_CAPSULE_UNDISCOVERED;
        }

        // Create or update a Marker for each Capsule
        $.each(capsules, function(index, capsule) {
            var marker;
            if (!collection.hasOwnProperty(capsule.data.id)) {
                // Create the Marker
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(capsule.data.lat, capsule.data.lng),
                    title: capsule.data.name,
                    icon: icon,
                    visible: visible,
                    capsuleId: capsule.data.id
                });
                // Add the Marker to the Map
                marker.setMap(map);
                // Add the Marker to the collection of Markers
                collection[capsule.data.id] = marker;
            } else {
                // Get the Marker that has already been created
                marker = collection[capsule.data.id];
                // Update the Marker data with the data from the server
                marker.setTitle(capsule.data.name);
                // Remove the listener
                google.maps.event.clearListeners(marker, 'click');
            }

            if (typeof marker !== 'undefined') {
                // Add the Marker InfoWindow event listener
                gmap.setupMarkerInfoWindow(marker, capsule.data, ((type === mapView.CAPSULE_UNDISCOVERED) ? true : false) /* isUndiscovered */);
            }
        });
    }

    /**
     * Removes a single Marker from a collection given an id
     */
    mapView.removeMarker = function(id, collection) {
        var marker = collection[id];
        marker.setMap(null);
        delete collection[id];
    }

    /**
     * Removes all the Markers in the specified collection
     *
     * TODO Rework so don't need to pass both the type and collection in
     */
    mapView.removeMarkers = function(type, collection) {
        // Remove all existing Markers
        $.each(collection, function(id, marker) {
            marker.setMap(null);
        });
        // Reinitialize Marker collection
        if (type === mapView.CAPSULE_OWNERSHIP) {
            mapView.ownedMarkers = {};
        } else if (type === mapView.CAPSULE_DISCOVERY) {
            mapView.discoveredMarkers = {};
        } else if (type === mapView.CAPSULE_UNDISCOVERED) {
            mapView.undiscoveredMarkers = {};
        }
    }

    /**
     * Renders the Capsule list
     */
    mapView.renderCapsuleList = function(href) {
        var container;
        var uri;
        if (href === "#tab-pane-discoveries") {
            container = $('#tab-pane-discoveries');
            uri = mapView.paginationUri.discoveries;
        } else {
            container = $('#tab-pane-capsules');
            uri = mapView.paginationUri.capsules;
        }
        $.ajax({
            type: 'GET',
            url: uri,
            beforeSend: function(jqXHR, settings) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').show();
            },
            complete: function(jqXHR, textStatus) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').hide();
            },
            success: function(data, textStatus, jqXHR) {
                container.html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                container.html("The list could not be retrieved");
            }
        });
    }

    /**
     * Renders the Capsule info
     */
    mapView.renderCapsuleInfo = function(container, id) {
        // Build the request data
        var requestData = {
            "data[id]": id
        }
        // Determine if a location will be submitted in the request
        if (typeof geoloc.coordinates !== 'undefined' && geoloc.coordinates != null && typeof geoloc.coordinates.latitude !== 'undefined' && typeof geoloc.coordinates.longitude !== 'undefined') {
            requestData["data[lat]"] = geoloc.coordinates.latitude;
            requestData["data[lng]"] = geoloc.coordinates.longitude;
        }
        // Send the request
        $.ajax({
            type: 'POST',
            url: "/capsules/view/",
            data: requestData,
            dataType: 'json',
            beforeSend: function(jqXHR, settings) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-body > .modal-loader').show();
            },
            complete: function(jqXHR, textStatus) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-body > .modal-loader').hide();
            },
            success: function(data, textStatus, jqXHR) {
                // Render the view
                if (data.hasOwnProperty('view')) {
                    container.html(data.view);
                }
                // Remove the Capsule from the undiscovered collection
                if (data.hasOwnProperty('newDiscovery')) {
                    // Remove the old Marker
                    if (mapView.undiscoveredMarkers.hasOwnProperty(data.newDiscovery.id)) {
                        mapView.removeMarker(data.newDiscovery.id, mapView.undiscoveredMarkers);
                    }

                    // Create the replacement Marker
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(data.newDiscovery.lat, data.newDiscovery.lng),
                        title: data.newDiscovery.name,
                        icon: mapView.ICON_CAPSULE_DISCOVERY,
                        visible: $('#toggle-discovered').prop('checked'),
                        capsuleId: data.newDiscovery.id
                    });
                    // Add the Marker to the Map
                    marker.setMap(gmap.map);
                    // Add the Marker to the collection of Markers
                    mapView.discoveredMarkers[data.newDiscovery.id] = marker;
                    // Add the event click listener
                    gmap.setupMarkerInfoWindow(marker, data.newDiscovery, false /* isUndiscovered */);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                container.html("The data could not be retrieved");
            }
        });
    }

    /**
     * Renders the Capsule editor
     */
    mapView.renderCapsuleEditor = function(container, id) {
        // Determine the URI
        var uri = "/capsules/edit/";
        if (typeof id !== 'undefined') {
            uri = uri + id;
        }

        // Fetch the view
        $.ajax({
            type: 'GET',
            url: uri,
            beforeSend: function(jqXHR, settings) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-body > .modal-loader').show();
            },
            complete: function(jqXHR, textStatus) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-body > .modal-loader').hide();
            },
            success: function(data, textStatus, jqXHR) {
                // Render content
                container.html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                container.html("The data could not be retrieved");
            }
        });
    }

    /**
     * Clears all the content in the specified container and renders the loader
     */
    mapView.renderLoader = function(container) {
        // Clear the content
        container.html('');
        // Build the loader markup
        var loader = $('<div/>', {
            class: 'modal-body'
        }).append($('<?php echo preg_replace('/\s\s+/', '', $this->element('loader')); ?>'));
        container.append(loader);
    }

    /**
     * Submits a request for editing a Capsule
     */
    mapView.submitCapsuleFormRequest = function(container, form) {
        // Submit the form
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            beforeSend: function(jqXHR, settings) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').show();
            },
            complete: function(jqXHR, textStatus) {
                container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').hide();
            },
            success: function(data, textStatus, jqXHR) {
                // Render the view
                if (data.hasOwnProperty('capsule')) {
                    // Check if the Capsule is new
                    var marker;
                    if (data.capsule.hasOwnProperty('isNew') && data.capsule.isNew == true) {
                        // Create the Marker
                        marker = new google.maps.Marker({
                            position: new google.maps.LatLng(data.capsule.lat, data.capsule.lng),
                            title: data.capsule.name,
                            icon: mapView.ICON_CAPSULE_OWNERSHIP,
                            visible: $('#toggle-owned').prop('checked'),
                            capsuleId: data.capsule.id
                        });
                        // Add the Marker to the Map
                        marker.setMap(gmap.map);
                        // Add the Marker to the collection of Markers
                        mapView.ownedMarkers[data.capsule.id] = marker;
                        // Remove the new Capsule Marker
                        mapView.newCapsuleMarker.setMap(null);
                        mapView.newCapsuleMarker.setAnimation(google.maps.Animation.DROP);
                    } else {
                        // Get the Marker that has already been created
                        marker = mapView.ownedMarkers[data.capsule.id];
                        // Update the Marker data with the data from the server
                        marker.setTitle(data.capsule.name);
                        // Remove the listener
                        google.maps.event.clearListeners(marker, 'click');
                    }

                    if (typeof marker !== 'undefined') {
                        // Add the Marker InfoWindow event listener
                        gmap.setupMarkerInfoWindow(marker, data.capsule, false /* isUndiscovered */);
                        google.maps.event.trigger(mapView.ownedMarkers[data.capsule.id], 'click');
                    }

                    // Open the previous modal
                    $('#modal-capsule-info').data('id', data.capsule.id);
                    $('#modal-capsule-info').modal('show');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.hasOwnProperty('responseText')) {
                    container.html(jqXHR.responseText);
                }
            }
        });
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
        
        // If a position update occurs, Discovery mode is on
        $('#toggle-discovery-mode').prop('checked', true);
        mapView.discoveryModeOn = true;

        // Update the user's location circle
        if (!gmap.locationCircle.getVisible()) {
            gmap.locationCircle.setVisible(true);
        }
        gmap.locationCircle.setCenter(new google.maps.LatLng(coordinates.latitude, coordinates.longitude));

        // Get the latitude and longitude
        mapView.getUndiscoveredMarkers(coordinates.latitude, coordinates.longitude, function (capsules) {
            mapView.populateMarkers(gmap.map, mapView.undiscoveredMarkers, capsules, mapView.CAPSULE_UNDISCOVERED, mapView.discoveryModeOn);
        });
    }

    /**
     * Callback for handling a geolocation error
     *
     * TODO Provide proper error messages (probably will use modals)
     */
    geoloc.onError = function(error) {
        if (error.code == error.PERMISSION_DENIED) {
            alert('User denied permission');
        } else if (error.code == error.POSITION_UNAVAILABLE) {
            alert('Could not retrieve the user location');
        } else if (error.code == error.TIMEOUT) {
            alert('Timeout');
        } else {
            alert('Unknown Error');
        }
    }
</script>
<script type="text/javascript">
    // The namespace
    var gmap = {};

    // The Map options
    gmap.mapOptions = {
        center: new google.maps.LatLng(47.618475, -122.365431),
        disableDoubleClickZoom: true,
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
                '<div class="map-info-window-container">'
                    + '<h3 class="text-format-overflow">' + capsule.name + '</h3>'
                    + '<div class="text-center">'
                        + '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-capsule-info" data-id="' + capsule.id + '" data-undiscovered="' + isUndiscovered + '">'
                            + (isUndiscovered ? 'Discover' : 'Open')
                        + '</button>'
                    + '</div>'
                + '</div>'
            );
            // Open the info window
            gmap.markerInfoWindow.open(gmap.map, marker);
        });
    }

    /**
     * Creates a custom control
     */
    gmap.createControl = function(map, position, attr, content, event, callback) {
        // Create the container
        var container = document.createElement('div');
        $(container).attr({
            'class': 'gmnoprint'
        });
        $(container).css({
            'margin': '5px',
            'z-index': '0',
            'position': 'absolute',
            'cursor': 'pointer',
            'right': '0px',
            'top': '0px'
        });

        // Create the outer element
        var outer = document.createElement('div');
        outer.className = 'gm-style-mtc';
        $(outer).attr(attr);
        $(outer).css({
            'float': 'left'
        });
        container.appendChild(outer);

        // Create the inner element
        var inner = document.createElement('div');
        $(inner).attr({
            'draggable': 'false'
        });
        $(inner).css({
            'direction': 'ltr',
            'overflow': 'hidden',
            'text-align': 'center',
            'position': 'relative',
            'color': 'rgb(86, 86, 86)',
            'font-family': 'Roboto, Arial, sans-serif',
            '-moz-user-select': 'none',
            'font-size': '11px',
            'background-color': 'rgb(255, 255, 255)',
            'padding': '1px 6px',
            'border-bottom-left-radius': '2px',
            'border-top-left-radius': '2px',
            'background-clip': 'padding-box',
            'border': '1px solid rgba(0, 0, 0, 0.15)',
            'box-shadow': '0px 1px 4px -1px rgba(0, 0, 0, 0.3)',
            'min-width': '22px'
        });
        inner.innerHTML = content;
        outer.appendChild(inner);

        google.maps.event.addDomListener(outer, event, callback);

        container.index = 1;
        map.controls[position].push(container);
    }

    /**
     * Adds a double-click listener to the object passed in for handling the new Capsule Marker
     */
    gmap.markerDropListener = function(instance, marker) {
        google.maps.event.addListener(instance, 'dblclick', function(e) {
            marker.setPosition(e.latLng);
            marker.setAnimation(google.maps.Animation.DROP);
            if (!marker.getMap()) {
                marker.setMap(gmap.map);
            }
        });
    }

    // Load the Map
    $(document).ready(function() {
        // Initialize the map
        gmap.map = new google.maps.Map(document.getElementById("map"), gmap.mapOptions);
        // Listeners for map idling
        google.maps.event.addListener(gmap.map, 'idle', function() {
            var bounds = gmap.map.getBounds();
            var latLngNE = bounds.getNorthEast();
            var latLngSW = bounds.getSouthWest();
            mapView.getMarkers(latLngNE.lat(), latLngNE.lng(), latLngSW.lat(), latLngSW.lng(), function(capsules, discoveries) {
                // Populate the Map with Markers
                mapView.populateMarkers(gmap.map, mapView.ownedMarkers, capsules, mapView.CAPSULE_OWNERSHIP, $('#toggle-owned').prop('checked'));
                mapView.populateMarkers(gmap.map, mapView.discoveredMarkers, discoveries, mapView.CAPSULE_DISCOVERY, $('#toggle-discovered').prop('checked'));
            });
        });
        // Listener for the new Capsule Marker click event
        google.maps.event.addListener(mapView.newCapsuleMarker, 'click', function() {
            // Set the content
            gmap.markerInfoWindow.setContent(
                '<div class="map-info-window-container">'
                    + '<h3>New Capsule</h3>'
                    + '<h4>You can drag this to finalize your position.  When you are ready, bury it.</h4>'
                    + '<div class="text-center">'
                        + '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-capsule-editor">'
                            + "Bury Here"
                        + '</button>'
                    + '</div>'
                + '</div>'
            );
            // Open the info window
            gmap.markerInfoWindow.open(gmap.map, mapView.newCapsuleMarker);
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
        // Create the listeners to handle dropping the new Capsule Marker
        gmap.markerDropListener(gmap.map, mapView.newCapsuleMarker);
        gmap.markerDropListener(gmap.locationCircle, mapView.newCapsuleMarker);
        // Create the custom control for centering on the user location
        gmap.createControl(
            gmap.map,
            google.maps.ControlPosition.TOP_RIGHT, 
            {
                'id': 'gmap-cntrl-location',
                'title': 'Go to My Location',
                'data-toggle': 'popover',
                'data-trigger': 'manual',
                'data-content': 'To center the map on your location, "Discovery mode" must be enabled.',
                'data-placement': 'bottom'
            },
            '<span class="glyphicon glyphicon-flag"></span>',
            'click',
            function() {
                if (mapView.discoveryModeOn == true) {
                    gmap.map.setCenter(new google.maps.LatLng(geoloc.coordinates.latitude, geoloc.coordinates.longitude));
                } else {
                    $('#gmap-cntrl-location').popover('show');
                    setTimeout(function(){
                        $('#gmap-cntrl-location').popover('hide');
                    }, 5000);
                }
            }
        );
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Disable Discovery Mode by default
        $('#toggle-discovery-mode').prop('checked', false);

        // Listener for toggling owned Capsules
        $('#toggle-owned, #toggle-discovered').change(function(e) {
            var markers;
            if ($(this).attr('id') === "toggle-owned") {
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
        $('#toggle-discovery-mode').change(function(e) {
            mapView.discoveryModeOn = $(this).prop('checked');
            if (mapView.discoveryModeOn == true) {
                if (navigator.geolocation) {
                    // Enable location watching and store the watchID
                    geoloc.watchId = navigator.geolocation.watchPosition(geoloc.onPositionUpdate, geoloc.onError, geoloc.options);
                    // If the user has not chosen to have their position watched, turn off Discovery mode
                    setTimeout(function() {
                        if (typeof geoloc.coordinates === 'undefined' || geoloc.coordinates == null) {
                            $('#toggle-discovery-mode').prop('checked', false);
                            mapView.discoveryModeOn = false;
                        }
                    }, 10000);
                } else {
                    alert('Geolocation not supported by the browser');
                }
            } else {
                // Stop watching for the geolocation
                navigator.geolocation.clearWatch(geoloc.watchId);
                // Remove the user's location circle
                gmap.locationCircle.setVisible(false);
                // Remove all existing Markers
                mapView.removeMarkers(mapView.CAPSULE_UNDISCOVERED, mapView.undiscoveredMarkers);
                // Clear out the stored coordinates
                geoloc.coordinates = null;
            }
        });

        // Handler for confirming a Capsule deletion
        $('#modal-capsule-info').on('click', '#capsule-delete-confirm-btn', function(e) {
            var container = $(this).closest('.modal');
            var id = $(this).data('id');
            
            if (id) {
                // Hide the popover
                $('#capsule-delete-btn').popover('hide');

                // Send a request to delete
                $.ajax({
                    type: 'POST',
                    url: "/capsules/delete/" + id,
                    beforeSend: function(jqXHR, settings) {
                        container.find('.modal-dialog > .modal-content > .modal-header > .modal-loader').show();
                    },
                    complete: function(jqXHR, textStatus) {
                        container.find('.modal-dialog > .modal-content > .modal-header > .modal-loader').hide();
                    },
                    success: function(data, textStatus, jqXHR) {
                        // Remove the deleted Marker
                        mapView.removeMarker(id, mapView.ownedMarkers);
                        // Close the modal
                        $('#modal-capsule-info').modal('hide');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Close the modal
                        $('#modal-capsule-info').modal('hide');
                    }
                });
            }
        });

        // Handler for canceling a Capsule deletion
        $('#modal-capsule-info').on('click', '#capsule-delete-cancel-btn', function(e) {
            $('#capsule-delete-btn').popover('hide');
        });

        // Listener for going to a point on the map
        $('#modal-capsule-list').on('click', '.anchor-map-goto', function(e) {
            $('#modal-capsule-list').modal('hide');
            var id = $(this).attr('data-id');
            var lat = $(this).attr('data-lat');
            var lng = $(this).attr('data-lng');
            gmap.map.setCenter(new google.maps.LatLng(lat, lng));
            gmap.map.setZoom(gmap.singleFocusZoom);
            // Show the Marker InfoWindow
            if (mapView.ownedMarkers.hasOwnProperty(id)) {
                google.maps.event.trigger(mapView.ownedMarkers[id], 'click');
            } else if (mapView.discoveredMarkers.hasOwnProperty(id)) {
                google.maps.event.trigger(mapView.discoveredMarkers[id], 'click');
            }
        });

        // Listener for the Capsule editor form submission
        $('#modal-capsule-editor').on('submit', '#CapsuleAddForm', function(e) {
            e.preventDefault();
            // Reference to the form
            var form = $(this);

            // Reference to the container
            var container = $(this).closest('.modal-content');

            // Get the LatLng
            var latLng = mapView.newCapsuleMarker.getPosition();
            if (typeof latLng !== 'undefined') {
                var lat = latLng.lat();
                var lng = latLng.lng();

                // Append the lat/lng inputs
                $('<input/>', {
                    type: 'hidden',
                    name: 'data[Capsule][lat]',
                    value: lat
                }).appendTo(form);
                $('<input/>', {
                    type: 'hidden',
                    name: 'data[Capsule][lng]',
                    value: lng
                }).appendTo(form);
            }

            // Submit the form request
            mapView.submitCapsuleFormRequest(container, form);
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Hide other modals when opening a new one
        $('.modal').on('show.bs.modal', function(e) {
            $('.modal').modal('hide');
        });

        // Handler for showing displaying an animation before opening the Discovery model
        $('#modal-capsule-info').on('show.bs.modal', function(e) {
            if (typeof e.relatedTarget !== 'undefined' && e.relatedTarget.dataset.hasOwnProperty('undiscovered')
                && e.relatedTarget.dataset.hasOwnProperty('id') && e.relatedTarget.dataset.undiscovered == "true"
            ) {
                e.preventDefault();
                $('#modal-animation').modal('show');
                setTimeout(function() {
                    $('#modal-capsule-info').data('id', e.relatedTarget.dataset.id);
                    $('#modal-capsule-info').data('undiscovered', true);
                    $('#modal-capsule-info').modal('show');
                }, 1000);
            }
        });

        // Clear modal content after being hidden
        $('#modal-capsule-info, #modal-capsule-editor').on('hidden.bs.modal', function(e) {
            // Get the content container
            var container = $(e.target).find('.modal-dialog > .modal-content');
            // Render the loader
            mapView.renderLoader(container);
        });

        // Modal Capsule list content after shown listener
        $('#modal-capsule-list').on('shown.bs.modal', function(e) {
            mapView.renderCapsuleList($(this).find('.modal-body > .nav-tabs > li.active > a').attr('href'));
        });

        // Lisenter for modal Capsule list tabs
        $('#modal-capsule-list a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            mapView.renderCapsuleList($(e.target).attr('href'));
        });

        // Handler for the content of the Capsule info modal
        $('#modal-capsule-info').on('shown.bs.modal', function(e) {
            var container = $(this).find('.modal-dialog > .modal-content');
            // Determine the id
            var id;
            if ($(this).data('id')) {
                id = $(this).data('id');
                $(this).removeData('id');
            } else if (typeof e.relatedTarget.dataset.id !== 'undefined') {
                id = e.relatedTarget.dataset.id;
            }
            // Unset the undiscovered flag
            if ($(this).data('undiscovered')) {
                $(this).removeData('undiscovered');
            }
            // Render the Capsule info markup
            mapView.renderCapsuleInfo(container, id);
        });

        // Handler for Capsule editor modal
        $('#modal-capsule-editor').on('shown.bs.modal', function(e) {
            if (typeof mapView.newCapsuleMarker === 'undefined') {
                return false;
            }

            var container = $(this).find('.modal-dialog > .modal-content');

            // Check if this is a CREATE or UPDATE
            var id;
            if (typeof e.relatedTarget.dataset.id !== 'undefined') {
                id = e.relatedTarget.dataset.id;
            }

            // Render the Capsule editor markup
            mapView.renderCapsuleEditor(container, id);
        });
    });
</script>
<div id="modal-capsule-list" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-capsule-list" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-label-capsule-list">My Collection</h4>
                <div class="modal-loader">
                    <div class="text-center">
                        <span class="glyphicon glyphicon-repeat"></span> Loading...
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#tab-pane-capsules" role="tab" data-toggle="tab">My Capsules</a></li>
                    <li><a href="#tab-pane-discoveries" role="tab" data-toggle="tab">My Discoveries</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-pane-capsules"></div>
                    <div class="tab-pane" id="tab-pane-discoveries"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-capsule-info" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-capsule-info" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <?php echo $this->element('loader'); ?>
            </div>
        </div>
    </div>
</div>
<div id="modal-capsule-editor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-capsule-editor" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <?php echo $this->element('loader'); ?>
            </div>
        </div>
    </div>
</div>
<div id="modal-animation" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-animation" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <h1 class="text-center">Discovering...</h1>
            </div>
        </div>
    </div>
</div>
<div id="map-controls">
    <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-success">
            <input type="checkbox" id="toggle-discovery-mode" autocomplete="off"> Discovery Mode
        </label>
    </div>
    <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default active">
            <input type="checkbox" id="toggle-owned" autocomplete="off" checked> My Capsules
        </label>
        <label class="btn btn-default active">
            <input type="checkbox" id="toggle-discovered" autocomplete="off" checked> My Discoveries
        </label>
    </div>
    <div class="btn-group">
        <button type="button" id="capsule_list" class="btn btn-info" data-toggle="modal" data-target="#modal-capsule-list">
            <span class="glyphicon glyphicon-list"></span> My Collection
        </button>
    </div>
</div>
<div id="map"></div>