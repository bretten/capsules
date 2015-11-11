/**
 * Contains functionality that goes with a form for editing Capsules
 *
 * @constructor
 * @author https://github.com/bretten
 */
var CapsuleEditor = function () {
    this.initializeGeolocator();
};

/**
 * The maximum upload file size
 *
 * @type {number}
 */
CapsuleEditor.prototype.maxUploadFileSize = 5120000;

/**
 * The maximum number of characters for a title
 *
 * @type {number}
 */
CapsuleEditor.prototype.maxTitleCharLimit = 255;

/**
 * The error message for when the file size has been exceeded
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageMaxUploadLimit = "The file size cannot exceed 5MB";

/**
 * The error message for when a file was not chosen
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageChooseFile = "Please choose a file.";

/**
 * The error message for when a Capsule name was not chosen
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageCapsuleNameEmpty = "Please enter a name.";

/**
 * The error message for when the Capsule name length exceeds the limit
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageCapsuleNameExceedsLimit = "The name cannot exceed 255 characters.";

/**
 * The error message for when a Memoir title is empty
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageMemoirTitleEmpty = "Please enter a title.";

/**
 * The error message for when a Memoir title length exceeds the limit
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageMemoirTitleExceedsLimit = "The title cannot exceed 255 characters.";

/**
 * The error message for when a Capsule location is not specified
 *
 * @type {string}
 */
CapsuleEditor.prototype.errorMessageLocationNotFound = "A location for the Capsule needs to be specified";

/**
 * Callback that executes after a file is chosen
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onChooseFileCallback = null;

/**
 * Callback that executes after a file has been set by the file input
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onFileReady = null;

/**
 * Geolocator used to determine user location
 *
 * @type {Geolocator|null}
 */
CapsuleEditor.prototype.geolocator = null;

/**
 * The element that will contain the map
 *
 * @type {HTMLElement|null}
 */
CapsuleEditor.prototype.mapElement = null;

/**
 * The map for locating the user
 *
 * @type {google.maps.Map|null}
 */
CapsuleEditor.prototype.map = null;

/**
 * The default options for the map
 *
 * @type {google.maps.MapOptions}
 */
CapsuleEditor.prototype.mapOptions = {
    center: new google.maps.LatLng(0, 0),
    disableDoubleClickZoom: true,
    draggable: false,
    keyboardShortcuts: false,
    scrollwheel: false,
    streetViewControl: false,
    zoom: 2
};

/**
 * The marker for designating the user location
 *
 * @type {google.maps.Marker|null}
 */
CapsuleEditor.prototype.newCapsuleMarker = null;

/**
 * Callback that executes when a location request is made
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onGeolocationRequestCallback = null;

/**
 * Callback that executes when the user does not answer geolocation permission prompt
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onGeolocationPermissionTimeoutCallback = null;

/**
 * Callback that executes when a location request is successful
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onGeolocationSuccessCallback = null;

/**
 * Callback that executes when a location error occurs
 *
 * @type {function|null}
 */
CapsuleEditor.prototype.onGeolocationErrorCallback = null;

/**
 * The amount of time (ms) to wait for a user response on the geolocation permission prompt before executing the
 * onGeolocationPermissionTimeoutCallback method
 *
 * @type {number}
 */
CapsuleEditor.prototype.geolocatorWaitTimeout = 3000;

/**
 * The map zoom level to be used when focusing on the user's location
 *
 * @type {number}
 */
CapsuleEditor.prototype.focusedZoomLevel = 15;

/**
 * Validates a Capsule
 * @param name The Capsule Name
 * @param lat The Capsule latitude
 * @param lng The Capsule longitude
 * @returns {{nameErrors: Array, locationErrors: Array}}
 */
CapsuleEditor.prototype.validateCapsule = function (name, lat, lng) {
    // Will hold the errors
    var nameErrors = [];
    var locationErrors = [];
    // Make sure the name is not empty
    if (!name.trim()) {
        nameErrors.push(this.errorMessageCapsuleNameEmpty);
    }
    // Make sure the name does not exceed the max length
    if (name.length > this.maxTitleCharLimit) {
        nameErrors.push(this.errorMessageCapsuleNameExceedsLimit);
    }
    // Make sure the location values are present
    if (!lat.trim() || !lng.trim()) {
        locationErrors.push(this.errorMessageLocationNotFound);
    }

    return {
        nameErrors: nameErrors,
        locationErrors: locationErrors
    };
};

/**
 * Validates a Memoir
 * @param title The Memoir title
 * @returns {{titleErrors: Array}}
 */
CapsuleEditor.prototype.validateMemoir = function (title) {
    // Will hold the errors
    var titleErrors = [];
    // Make sure the title is not empty
    if (!title.trim()) {
        titleErrors.push(this.errorMessageMemoirTitleEmpty);
    }
    // Make sure the title does not exceed the max length
    if (title.length > this.maxTitleCharLimit) {
        titleErrors.push(this.errorMessageMemoirTitleExceedsLimit);
    }

    return {
        titleErrors: titleErrors
    };
};

/**
 * Validates a file input
 *
 * @param fileInput The file input to be validated
 * @returns {Array} The file errors
 */
CapsuleEditor.prototype.validateFileInput = function (fileInput) {
    // Will hold the error messages
    var fileErrors = [];
    // Validate the file
    var file = fileInput.prop('files')[0];
    if (typeof file !== 'undefined') {
        if (file.size > this.maxUploadFileSize) {
            fileErrors.push(this.errorMessageMaxUploadLimit);
        }
    } else {
        fileErrors.push(this.errorMessageChooseFile);
    }
    return fileErrors;
};

/**
 * Method that should be called when a file input changes.  Executes callbacks before and after the file is loaded
 * into the browser
 *
 * @param fileInput The file input that was changed
 */
CapsuleEditor.prototype.onFileInputChange = function (fileInput) {
    // Get the file
    var file = fileInput.prop('files')[0];
    // Get a FileReader
    var fileReader = new FileReader();
    // Read the file
    fileReader.readAsDataURL(file);
    // Set the progress callback
    if ($.isFunction(this.onChooseFileCallback)) {
        fileReader.onprogress = this.onChooseFileCallback;
    }
    // Set the on load callback
    if ($.isFunction(this.onFileReady)) {
        fileReader.onload = this.onFileReady;
    }
};

/**
 * Initializes the map on the specified element
 *
 * @param mapElement The DOM element to add the map to
 */
CapsuleEditor.prototype.initializeMap = function (mapElement) {
    this.mapElement = mapElement;
    this.map = new google.maps.Map(this.mapElement, this.mapOptions);
};

/**
 * Initializes the Geolocator and sets the callbacks
 */
CapsuleEditor.prototype.initializeGeolocator = function () {
    this.geolocator = new Geolocator();
    // Set the timeout
    this.geolocator.permissionTimeout = this.geolocatorWaitTimeout;
    // Set the callback that executes when a location request is made
    this.geolocator.onRequestPositionCallback = this.onGeolocationRequest.bind(this);
    // Set the callback that executes if the geolocation permission prompt is not answered by the user
    this.geolocator.onPermissionTimeoutCallback = this.onGeolocationPermisssionTimeout.bind(this);
    // Set the callback that executes on a successful location request
    this.geolocator.onPositionUpdateCallback = this.onGeolocationSuccess.bind(this);
    // Set the callback that executes on a location request error
    this.geolocator.onErrorCallback = this.onGeolocationError.bind(this);
};

/**
 * Callback method that executes on a successful location request
 *
 * @param lat The latitude returned from the geolcation API
 * @param lng The longitude returned from the geolcation API
 */
CapsuleEditor.prototype.onGeolocationSuccess = function (lat, lng) {
    if ($.isFunction(this.onGeolocationSuccessCallback)) {
        this.onGeolocationSuccessCallback(lat, lng);
    }

    this.focusOnLocation(this.focusedZoomLevel);
    this.updateUserPosition(lat, lng);
};

/**
 * Callback method that executes if the geolocation permission prompt is not answered by the user
 */
CapsuleEditor.prototype.onGeolocationPermisssionTimeout = function () {
    if ($.isFunction(this.onGeolocationPermissionTimeoutCallback)) {
        this.onGeolocationPermissionTimeoutCallback(this.geolocator.isCurrentPositionAvailable());
    }
};

/**
 * Callback method that executes when a location request is made
 */
CapsuleEditor.prototype.onGeolocationRequest = function () {
    this.geolocator.clearCurrentPosition();
    this.removeUserPosition();
    if ($.isFunction(this.onGeolocationRequestCallback)) {
        this.onGeolocationRequestCallback();
    }
};

/**
 * Callback method that executes when a location request results in an error
 *
 * @type {string} The error message
 */
CapsuleEditor.prototype.onGeolocationError = function (errorMessage) {
    if ($.isFunction(this.onGeolocationErrorCallback)) {
        this.onGeolocationErrorCallback(errorMessage);
    }
};

/**
 * Requests the current position from the Geolocation API
 */
CapsuleEditor.prototype.requestPosition = function () {
    this.geolocator.getCurrentPosition();
};

/**
 * Updates the user location on the map
 *
 * @param lat The user's latitude
 * @param lng The user's longitude
 */
CapsuleEditor.prototype.updateUserPosition = function (lat, lng) {
    this.centerMap(lat, lng);
    this.setMarkerForNewCapsule(lat, lng);
};

/**
 * Removes the user location marker from the map
 */
CapsuleEditor.prototype.removeUserPosition = function () {
    if (this.newCapsuleMarker != null && this.newCapsuleMarker instanceof google.maps.Marker) {
        this.newCapsuleMarker.setMap(null);
    }
};

/**
 * Sets the position of the Capsule is being created
 *
 * @param lat The Capsule latitude
 * @param lng The Capsule longitude
 */
CapsuleEditor.prototype.setMarkerForNewCapsule = function (lat, lng) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    if (this.newCapsuleMarker != null && this.newCapsuleMarker instanceof google.maps.Marker) {
        this.newCapsuleMarker.setMap(this.map);
        this.newCapsuleMarker.setAnimation(google.maps.Animation.DROP);
        this.newCapsuleMarker.setPosition({lat: lat, lng: lng});
    } else {
        this.newCapsuleMarker = new google.maps.Marker({
            map: this.map,
            draggable: false,
            animation: google.maps.Animation.DROP,
            position: {lat: lat, lng: lng}
        });
    }
};

/**
 * Centers the map on the specified coordinates
 *
 * @param lat The new latitude
 * @param lng The new longitude
 */
CapsuleEditor.prototype.centerMap = function (lat, lng) {
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
CapsuleEditor.prototype.focusOnLocation = function (zoom) {
    if (this.map == null || !this.map instanceof google.maps.Map) {
        return;
    }

    this.map.setZoom(zoom);
};
