/**
 * Represents a Map that displays Capsules
 *
 * @constructor
 * @author https://github.com/bretten
 */
var CapsuleMap = function () {
    this.initializeGeolocator();
};

/**
 * Geolocator used to determine user location
 *
 * @type {Geolocator|null}
 */
CapsuleMap.prototype.geolocator = null;

/**
 * The element that will contain the Map
 *
 * @type {HTMLElement|null}
 */
CapsuleMap.prototype.mapElement = null;

/**
 * The Map object
 *
 * @type {google.maps.Map|null}
 */
CapsuleMap.prototype.map = null;

/**
 * The default options for the Map
 *
 * @type {google.maps.MapOptions}
 */
CapsuleMap.prototype.mapOptions = {
    center: new google.maps.LatLng(0, 0),
    disableDoubleClickZoom: true,
    keyboardShortcuts: false,
    streetViewControl: false,
    zoom: 2
};

/**
 * A collection of Marker icons
 *
 * @type {object}
 */
CapsuleMap.prototype.markerIcons = {
    yellowDot: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",
    blueDot: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
    redDot: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
    greenDot: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
    purpleDot: "https://maps.google.com/mapfiles/ms/icons/purple-dot.png"
};

/**
 * The Marker for designating the user location
 *
 * @type {google.maps.Marker|null}
 */
CapsuleMap.prototype.userMarker = null;

/**
 * The Circle that surrounds the user's position
 *
 * @type {google.maps.Circle|null}
 */
CapsuleMap.prototype.userLocationCircle = null;

/**
 * Callback that executes when a location request is made
 *
 * @type {function|null}
 */
CapsuleMap.prototype.onGeolocationRequestCallback = null;

/**
 * Callback that executes when the user does not answer the geolocation permission prompt
 *
 * @type {function|null}
 */
CapsuleMap.prototype.onGeolocationPermissionTimeoutCallback = null;

/**
 * Callback that executes when a location request is successful
 *
 * @type {function|null}
 */
CapsuleMap.prototype.onGeolocationSuccessCallback = null;

/**
 * Callback that executes when a location error occurs
 *
 * @type {function|null}
 */
CapsuleMap.prototype.onGeolocationErrorCallback = null;

/**
 * The amount of time (ms) to wait for a user response on the geolocation permission prompt before executing the
 * onGeolocationPermissionTimeoutCallback method
 *
 * @type {number}
 */
CapsuleMap.prototype.geolocatorWaitTimeout = 3000;

/**
 * The Map zoom level to be used when focusing on a specific location
 *
 * @type {number}
 */
CapsuleMap.prototype.focusedZoomLevel = 15;

/**
 * The minimum map zoom level that is still considered "focused" on a location
 *
 * @type {number}
 */
CapsuleMap.prototype.minFocusedZoomLevel = 5;

/**
 * Callback that executes when a Marker is clicked
 *
 * @type {function|null}
 */
CapsuleMap.prototype.onMarkerClickCallback = null;

/**
 * Interval ID belonging to the interval that checks the server to see if the client-side
 * Capsules have changed
 *
 * @type {number|null}
 */
CapsuleMap.prototype.capsuleUpdateIntervalId = null;

/**
 * The default length in milliseconds of the interval between checks to the server for Capsule updates
 *
 * @type {number}
 */
CapsuleMap.prototype.capsuleUpdateIntervalLength = 15000;

/**
 * The client-side collection tag for Capsules
 *
 * @type {string|null}
 */
CapsuleMap.prototype.ctagCapsules = null;

/**
 * The client-side collection tag for Discovery Capsules
 *
 * @type {string|null}
 */
CapsuleMap.prototype.ctagDiscoveries = null;

/**
 * The client-side collection of Capsules
 *
 * @type {object|null}
 */
CapsuleMap.prototype.capsuleCollection = null;

/**
 * The client-side collection of Discovery Capsules
 *
 * @type {object|null}
 */
CapsuleMap.prototype.discoveryCollection = null;

/**
 * Initializes the map on the specified element
 *
 * @param mapElement The DOM element to add the map to
 */
CapsuleMap.prototype.initializeMap = function (mapElement) {
    this.mapElement = mapElement;
    this.map = new google.maps.Map(this.mapElement, this.mapOptions);
};

/**
 * Initializes the Geolocator and sets the callbacks
 */
CapsuleMap.prototype.initializeGeolocator = function () {
    this.geolocator = new Geolocator();
    // Set the timeout
    this.geolocator.permissionTimeout = this.geolocatorWaitTimeout;
    // Set the callback that executes when a location request is made
    this.geolocator.onRequestPositionCallback = this.onGeolocationRequest.bind(this);
    // Set the callback that executes if the geolocation permission prompt is not answered by the user
    this.geolocator.onPermissionTimeoutCallback = this.onGeolocationPermissionTimeout.bind(this);
    // Set the callback that executes on a successful location request
    this.geolocator.onPositionUpdateCallback = this.onGeolocationSuccess.bind(this);
    // Set the callback that executes on a location request error
    this.geolocator.onErrorCallback = this.onGeolocationError.bind(this);
};

/**
 * Initializes the Circle that bounds the user's current location
 *
 * @param options google.maps.CircleOptions that determines the appearances of the Circle
 */
CapsuleMap.prototype.initializeUserLocationCircle = function (options) {
    this.userLocationCircle = new google.maps.Circle(options);
};

/**
 * Callback method that executes on a successful location request
 *
 * @param lat The latitude returned from the geolcation API
 * @param lng The longitude returned from the geolcation API
 */
CapsuleMap.prototype.onGeolocationSuccess = function (lat, lng) {
    if ($.isFunction(this.onGeolocationSuccessCallback)) {
        this.onGeolocationSuccessCallback(lat, lng);
    }

    // Focus the Map on the user's location if it is not within the current Map bounds
    this.focusOnLocation(lat, lng);
    // Update the Marker indicating the user's location
    this.setUserMarker(lat, lng);
    // Update the position of the user's location circle
    this.setUserLocationCirclePosition(lat, lng);
};

/**
 * Callback method that executes if the geolocation permission prompt is not answered by the user
 */
CapsuleMap.prototype.onGeolocationPermissionTimeout = function () {
    if ($.isFunction(this.onGeolocationPermissionTimeoutCallback)) {
        this.onGeolocationPermissionTimeoutCallback(this.geolocator.isCurrentPositionAvailable());
    }
};

/**
 * Callback method that executes when a location request is made
 */
CapsuleMap.prototype.onGeolocationRequest = function () {
    // Clear the Geolocator's current position
    this.geolocator.clearCurrentPosition();
    // Remove the user's location marker from the map
    this.removeUserPosition();
    // Hide the location circle
    this.hideUserLocationCircle();
    // Execute the on request callback
    if ($.isFunction(this.onGeolocationRequestCallback)) {
        this.onGeolocationRequestCallback();
    }
};

/**
 * Callback method that executes when a location request results in an error
 *
 * @type {string} The error message
 */
CapsuleMap.prototype.onGeolocationError = function (errorMessage) {
    if ($.isFunction(this.onGeolocationErrorCallback)) {
        this.onGeolocationErrorCallback(errorMessage);
    }
};

/**
 * Requests the current position once from the Geolocation API
 */
CapsuleMap.prototype.requestPosition = function () {
    this.geolocator.getCurrentPosition();
};

/**
 * Continually requests the current position from the Geolocation API
 */
CapsuleMap.prototype.startPositionUpdateListener = function () {
    this.geolocator.listenForPositionUpdates();
};

/**
 * Stops listening for position updates from the Geolocation API
 */
CapsuleMap.prototype.stopPositionUpdateListener = function () {
    this.geolocator.stopListening();
};

/**
 * Removes the user location Marker from the Map
 */
CapsuleMap.prototype.removeUserPosition = function () {
    if (this.userMarker != null && this.userMarker instanceof google.maps.Marker) {
        this.userMarker.setMap(null);
    }
};

/**
 * Sets the position of the user's current location on the Map
 *
 * @param lat The latitude of the user's position
 * @param lng The longitude of the user's position
 */
CapsuleMap.prototype.setUserMarker = function (lat, lng) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    if (this.userMarker != null && this.userMarker instanceof google.maps.Marker) {
        this.userMarker.setMap(this.map);
        this.userMarker.setAnimation(google.maps.Animation.DROP);
        this.userMarker.setPosition({lat: lat, lng: lng});
    } else {
        this.userMarker = new google.maps.Marker({
            map: this.map,
            draggable: false,
            animation: google.maps.Animation.DROP,
            position: {lat: lat, lng: lng}
        });
    }
};

/**
 * Sets the user location Circle to visible and centers it on the specified location
 *
 * @param lat The new latitude
 * @param lng The new longitude
 */
CapsuleMap.prototype.setUserLocationCirclePosition = function (lat, lng) {
    if (this.userLocationCircle != null && this.userLocationCircle instanceof google.maps.Circle) {
        // Show the Circle if it is hidden
        if (!this.userLocationCircle.getVisible()) {
            this.userLocationCircle.setVisible(true);
        }
        // Set the location of the circle
        this.userLocationCircle.setCenter(new google.maps.LatLng(lat, lng));
    }
};

/**
 * Hides the user location Circle if it is visible
 */
CapsuleMap.prototype.hideUserLocationCircle = function () {
    if (this.userLocationCircle != null && this.userLocationCircle instanceof google.maps.Circle) {
        if (this.userLocationCircle.getVisible()) {
            this.userLocationCircle.setVisible(false);
        }
    }
};

/**
 * Centers the Map on the specified coordinates
 *
 * @param lat The new latitude
 * @param lng The new longitude
 */
CapsuleMap.prototype.centerMap = function (lat, lng) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    this.map.panTo({lat: lat, lng: lng});
};

/**
 * Zooms the map to the specified level
 *
 * @param zoom The zoom level
 */
CapsuleMap.prototype.zoomMap = function (zoom) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    this.map.setZoom(zoom);
};

/**
 * Focuses the Map on the specified location if it is not within the Map bounds or if the Map is zoomed too far out
 *
 * @param lat The new latitude
 * @param lng The new longitude
 */
CapsuleMap.prototype.focusOnLocation = function (lat, lng) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    if (!this.map.getBounds().contains(new google.maps.LatLng(lat, lng))
        || this.map.getZoom() < this.minFocusedZoomLevel) {
        // Zoom in on the location
        this.zoomMap(this.focusedZoomLevel);
        // Center the map on the location
        this.centerMap(lat, lng);
    }
};

/**
 * Starts an interval that checks to see if the Capsules have changed on the server-side
 */
CapsuleMap.prototype.startCapsuleUpdateInterval = function () {
    // Start the interval
    this.capsuleUpdateIntervalId = window.setInterval(function () {
        // Check for Capsule updates
        this.requestCtag(CapsuleType.Capsule);
        // Check for Discovery updates
        this.requestCtag(CapsuleType.Discovery);
    }.bind(this), this.capsuleUpdateIntervalLength);
};

/**
 * Stops the interval that checks the server for Capsule updates
 */
CapsuleMap.prototype.stopUserCapsulesListener = function () {
    window.clearInterval(this.capsuleUpdateIntervalId);
    this.capsuleUpdateIntervalId = null;
};

/**
 * Requests the collection tag from the server.  If the server's collection tag differs from the
 * client's collection tag, requests the server's Capsules
 *
 * @param capsuleType The type of Capsules
 */
CapsuleMap.prototype.requestCtag = function (capsuleType) {
    // The URL to request the collection tag from
    var url;
    // The client's collection tag
    var clientCtag;
    // Determine what kind of Capsules
    if (capsuleType == CapsuleType.Capsule) {
        url = "/api/ctag/capsules";
        clientCtag = this.ctagCapsules;
    } else if (capsuleType == CapsuleType.Discovery) {
        url = "/api/ctag/discoveries";
        clientCtag = this.ctagDiscoveries;
    } else {
        return;
    }

    // Send a request to the server for the collection tag
    $.ajax({
        context: this,
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if (data.hasOwnProperty("data") && data.data.hasOwnProperty("ctag")) {
                // Get the server collection tag from the data
                var serverCtag = data.data.ctag;
                // Compare the client and server collection tags
                if (serverCtag != clientCtag) {
                    // The collection tags differ, so update the client one
                    if (capsuleType == CapsuleType.Capsule) {
                        this.ctagCapsules = serverCtag;
                    } else {
                        this.ctagDiscoveries = serverCtag;
                    }
                    // Request the server Capsules
                    this.requestCapsules(capsuleType);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            return false;
        }
    });
};

/**
 * Requests the Capsules from the server
 *
 * @param capsuleType The type of Capsules
 */
CapsuleMap.prototype.requestCapsules = function (capsuleType) {
    // The URL to request the Capsules from
    var url;
    // Determine which type of Capsules to request
    if (capsuleType == CapsuleType.Capsule) {
        url = "/api/capsules";
    } else if (capsuleType == CapsuleType.Discovery) {
        url = "/api/discoveries";
    } else {
        return;
    }

    // Send a request to the server for the Capsules
    $.ajax({
        context: this,
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if (data.hasOwnProperty("data") && data.data.hasOwnProperty("capsules")) {
                // Remove Capsules that are no longer present on the server
                this.removeCapsulesNotOnServer(capsuleType, data.data.capsules);
                // Populate the server Capsule Markers onto the Map
                this.addNewCapsulesFromServer(capsuleType, data.data.capsules);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            return false;
        }
    });
};

/**
 * Given a collection of Capsules, adds them as Markers to the Map
 *
 * @param capsuleType The type of Capsule that is being added to the Map
 * @param capsules The collection of Capsules
 */
CapsuleMap.prototype.populateCapsuleMarkers = function (capsuleType, capsules) {
    for (var key in capsules) {
        if (capsules.hasOwnProperty(key)) {
            // Add the Marker
            capsules[key].Marker = this.addCapsuleAsMarker(capsuleType, capsules[key]);
        }
    }
};

/**
 * Given the collection of Capsules from the server, compares the server-side collection to the client-side collection
 * and removes any client-side Capsules that are no longer on the server
 *
 * @param capsuleType The type of Capsules being compared
 * @param serverCapsules The collection of Capsules from the server
 */
CapsuleMap.prototype.removeCapsulesNotOnServer = function (capsuleType, serverCapsules) {
    // The collection of client-side Capsules
    var collection;
    // Determine the type of Capsules being compared
    if (capsuleType == CapsuleType.Capsule) {
        collection = this.capsuleCollection;
    } else if (capsuleType == CapsuleType.Discovery) {
        collection = this.discoveryCollection;
    } else {
        return;
    }

    // Iterate through the client-side Capsules
    for (var key in collection.objects) {
        // Check if the client-side Capsule is not on the server
        if (collection.hasKey(key) && !serverCapsules.hasOwnProperty(key)) {
            // It is not on the server, so remove the Marker and any associated listeners
            if (collection.get(key).hasOwnProperty("Marker")) {
                // Remove any Marker listeners
                google.maps.event.clearInstanceListeners(collection.get(key).Marker);
                // Remove the Marker
                this.removeMarker(collection.get(key).Marker);
            }
            // Remove the entry from the collection
            collection.remove(key);
        }
    }
};

/**
 * Given a collection of Capsules from the server, compares them to the client-side collection and adds any server-side
 * Capsules that are not present on the client-side
 *
 * @param capsuleType The type of Capsules being compared
 * @param serverCapsules The collection of Capsules from the server
 */
CapsuleMap.prototype.addNewCapsulesFromServer = function (capsuleType, serverCapsules) {
    // The collection of client-side Capsules
    var collection;
    // Determine the type of Capsules being compared
    if (capsuleType == CapsuleType.Capsule) {
        collection = this.capsuleCollection;
    } else if (capsuleType == CapsuleType.Discovery) {
        collection = this.discoveryCollection;
    } else {
        return;
    }

    // Iterate through the server-side Capsules
    for (var key in serverCapsules) {
        // Check if the Capsule is present on the server but not on the client
        if (serverCapsules.hasOwnProperty(key) && !collection.hasKey(key)) {
            // Add the Capsule to the client-side collection
            collection.add(key, serverCapsules[key]);
            // Add a corresponding Marker to the map
            collection.get(key).Marker = this.addCapsuleAsMarker(capsuleType, collection.get(key));
        }
    }
};

/**
 * Adds the specified Capsule to the Map as a Marker
 *
 * @param capsuleType The type of Capsule being added
 * @param capsule The Capsule object
 * @returns {google.maps.Marker} The newly added Capsule Marker
 */
CapsuleMap.prototype.addCapsuleAsMarker = function (capsuleType, capsule) {
    // The Marker icon
    var icon;
    // Determine the Marker icon to use
    if (capsuleType == CapsuleType.Capsule) {
        icon = this.markerIcons.yellowDot;
    } else if (capsuleType == CapsuleType.Discovery) {
        icon = this.markerIcons.blueDot;
    } else {
        return null;
    }

    // Initialize the Marker
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(capsule.Capsule.lat, capsule.Capsule.lng),
        title: capsule.Capsule.name,
        icon: icon,
        visible: true,
        animation: google.maps.Animation.DROP,
        capsuleId: capsule.Capsule.id
    });

    // Add the Marker to the Map
    marker.setMap(this.map);

    // Add a click listener to the Marker
    this.addMarkerClickListener(marker);

    return marker;
};

/**
 * Adds a click listener to the specified Marker that executes the onMarkerClick callback
 *
 * @param marker The Marker to add the click listener to
 */
CapsuleMap.prototype.addMarkerClickListener = function (marker) {
    if (marker == null || !marker instanceof google.maps.Marker) {
        return;
    }

    // Make sure the onMarkerClick callback method is set
    if (this.onMarkerClickCallback != null && typeof this.onMarkerClickCallback === "function") {
        var self = this;
        google.maps.event.addListener(marker, 'click', function () {
            // Execute the callback
            self.onMarkerClickCallback(marker.capsuleId);
            // Animate the Marker to indicate it was clicked
            marker.setAnimation(google.maps.Animation.BOUNCE);
            // Stop the animation after a delay
            setTimeout(function () {
                marker.setAnimation(null);
            }, 1400 /* Equivalent to two bounces */);
        });
    }
};

/**
 * Removes the specified Marker from the Map
 *
 * @param marker The Marker to remove from the Map
 */
CapsuleMap.prototype.removeMarker = function (marker) {
    if (marker == null || !marker instanceof google.maps.Marker) {
        return;
    }

    marker.setMap(null);
    marker = undefined;
};

/**
 * Animates the specified Marker with the specified ID and executes the specified callback after the animation
 * has ended
 *
 * @param capsuleType The Capsule type of the Capsule that corresponds to the Marker
 * @param id The ID of the Capsule that corresponds to the Marker
 * @param callback The callback to execute after the animation has ended
 */
CapsuleMap.prototype.animateMarkerById = function (capsuleType, id, callback) {
    // The collection that contains the Marker to animate
    var collection;
    // Determine which type of Capsule Marker is being animated
    if (capsuleType == CapsuleType.Capsule) {
        collection = this.capsuleCollection;
    } else if (capsuleType == CapsuleType.Discovery) {
        collection = this.discoveryCollection;
    } else {
        return;
    }

    // Get the Capsule object
    var capsule = collection.get(id);
    // Animate the Marker if it can be found
    if (capsule.hasOwnProperty("Marker") && capsule.Marker instanceof google.maps.Marker) {
        // Animate the Marker
        capsule.Marker.setAnimation(google.maps.Animation.BOUNCE);
        // Stop the animation after a delay
        setTimeout(function () {
            capsule.Marker.setAnimation(null);
            // Execute the callback
            callback();
        }, 1400 /* Equivalent to two bounces */);
    }
};
