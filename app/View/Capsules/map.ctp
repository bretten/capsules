<script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript" src="/js/CapsuleType.js"></script>
<script type="text/javascript" src="/js/Geolocator.js"></script>
<script type="text/javascript" src="/js/Collection.js"></script>
<script type="text/javascript" src="/js/CapsuleMap.js"></script>
<script type="text/javascript">
    // Define the "namespace"
    var map = {};

    // See if an initial focus location was passed in
    map.initialLat = <?= isset($lat) && is_numeric($lat) ? $lat : "undefined"; ?>;
    map.initialLng = <?= isset($lng) && is_numeric($lng) ? $lng : "undefined"; ?>;
    map.initialFocusType = "<?= isset($focusType) && $focusType ? $focusType : ""; ?>";
    map.initialFocusId = <?= isset($focusId) && is_numeric($focusId) ? $focusId : "undefined"; ?>;

    $(document).ready(function () {
        // Modal for viewing Capsules
        map.modalCapsule = $('#modal-capsule');
        // Content container in modal for viewing Capsules
        map.modalCapsuleContentContainer = map.modalCapsule.find('#modal-capsule-content');
        // Loading indicator in modal for viewing Capsules
        map.modalCapsuleLoadingIndicator = map.modalCapsule.find('.loading-indicator');
        // Modal for viewing list of recent discoveries
        map.modalCapsuleList = $('#modal-capsule-list');
        // List group in modal for viewing recent discoveries
        map.modalCapsuleListGroup = $('#modal-capsule-list-group');
        // List group item that all recent discovery items clone their markup from
        map.modalCapsuleListItem = $('#modal-capsule-list-item');
        // Counter that records how many Capsules have been found in this current session
        map.capsulesFoundCounter = $('#capsules-found-counter');
        // Button that opens the list of recent discoveries
        map.capsulesFoundButton = $('#capsules-found-btn');
        // Button that starts the Capsule locator
        map.startLocatorButton = $('#start-locator-btn');
        // Message indicating if there have been any Capsules found
        map.noDiscoveriesMessage = $('#no-discoveries-message');
        // Displays location-related errors
        map.geolocationRequestErrorContainer = $("#location-error-container");

        // Activates the locator button
        map.activateLocatorButton = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("active btn-success");
            btn.removeClass("btn-warning btn-info");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Searching for Capsules..."); ?>');
        };
        // Deactivates the locator button
        map.deactivateLocatorButton = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("btn-warning");
            btn.removeClass("active btn-success btn-info");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Start Searching for Capsules"); ?>');
        };
        // Sets the locator button to indicate it is busy
        map.setLocatorButtonBusy = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("btn-info");
            btn.removeClass("active btn-success btn-warning");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Starting up..."); ?>');
        };
        // Sets the location error container
        map.setLocationError = function (markup) {
            map.geolocationRequestErrorContainer.html(markup);
            map.geolocationRequestErrorContainer.removeClass('hidden');
        };
        // Hides the location error container
        map.clearLocationError = function () {
            map.geolocationRequestErrorContainer.html("");
            map.geolocationRequestErrorContainer.addClass('hidden');
        };
        // Focuses on the specified coordinates and animates the specified Capsule
        map.delayedFocusOnLocation = function (lat, lng, type, id, animationDelay) {
            // Focus on the location after a delay
            setTimeout(function () {
                // Zoom in on the location
                map.capsuleMap.zoomMap(17);
                // Center the map on the location
                map.capsuleMap.centerMap(lat, lng);
                // If there is a Capsule ID, animate the Marker after a delay
                if (type != undefined && id != undefined) {
                    setTimeout(function () {
                        map.capsuleMap.animateMarkerById(type, id, function () {
                            map.capsuleMap.onMarkerClickCallback(id);
                        });
                    }, animationDelay);
                }
            }, 100);
        };
        // Adds Capsules to the list of recent discoveries
        map.addRecentDiscoveries = function (capsules) {
            var i = 0;
            for (var key in capsules) {
                if (capsules.hasOwnProperty(key)) {
                    // Clone the base list item
                    var item = map.modalCapsuleListItem.clone();
                    // Remove the ID attribute
                    item.removeAttr("id");
                    // Set the Capsule ID, latitude, and longitude on data attributes
                    item.attr('data-id', capsules[key].Capsule.id)
                        .attr('data-lat', capsules[key].Capsule.lat)
                        .attr('data-lng', capsules[key].Capsule.lng);
                    // Add the name of the Capsule to the list item
                    item.html(capsules[key].Capsule.name);
                    // Add it to the list of recent discoveries
                    (function (element, index) {
                        setTimeout(function () {
                            element.removeClass('hidden').hide().prependTo(map.modalCapsuleListGroup).fadeIn(1000);
                        }, index * 500);
                    })(item, i);
                    i++;
                }
            }
        };
        // Discovers all Capsules nearby the specified location
        map.discoverNearbyCapsules = function (lat, lng) {
            // Send a request to look for new Capsules
            $.ajax({
                type: 'POST',
                url: '/api/discoveries/',
                data: {'data[lat]': lat, 'data[lng]': lng},
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.status === 200 && data !== 'undefined') {
                        if (data.hasOwnProperty("data") && data.data.hasOwnProperty("capsules")) {
                            // If the message indicating no Capsules have been found yet is still showing, hide it
                            if (!map.noDiscoveriesMessage.hasClass("hidden")) {
                                map.noDiscoveriesMessage.addClass("hidden");
                            }
                            // Get the number of Capsules discovered
                            var capsuleCount = Object.keys(data.data.capsules).length;
                            // Update the counter
                            map.capsulesFoundCounter.html(parseInt(map.capsulesFoundCounter.html()) + capsuleCount);
                            // Add the newly discovered Capsules to the map
                            map.capsuleMap.addNewCapsulesFromServer(CapsuleType.Discovery, data.data.capsules);
                            // Open the modal
                            map.modalCapsuleList.modal('show');
                            // Append new Capsules to the list of recent discoveries
                            setTimeout(function () {
                                map.addRecentDiscoveries(data.data.capsules);
                            }, 200);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    return false;
                }
            });
        };

        // Initialize the CapsuleMap
        map.capsuleMap = new CapsuleMap();
        // Initialize the map element
        map.capsuleMap.initializeMap(document.getElementById("capsule-locator-map"));
        // Initialize the user location circle
        map.capsuleMap.initializeUserLocationCircle({
            strokeColor: '#A4C639',
            strokeOpacity: 0.1,
            strokeWeight: 1,
            fillColor: '#A4C639',
            fillOpacity: 0.1,
            map: map.capsuleMap.map,
            visible: false,
            radius: <?= Configure::read('Map.UserLocation.DiscoveryRadius'); ?> // meters
        });

        // Start interval that checks for Capsules updates
        map.capsuleMap.startCapsuleUpdateInterval();

        // Called when a Capsule Marker is clicked
        map.capsuleMap.onMarkerClickCallback = function (capsuleId) {
            $.ajax({
                type: 'GET',
                url: "/api/capsule/" + capsuleId,
                beforeSend: function (jqXHR, settings) {
                    // Show the loading indicator
                    map.modalCapsuleLoadingIndicator.removeClass("hidden");
                    // Open the modal
                    map.modalCapsule.modal('show');
                },
                complete: function (jqXHR, textStatus) {
                    // Hide the loading indicator
                    map.modalCapsuleLoadingIndicator.addClass("hidden");
                },
                success: function (data, textStatus, jqXHR) {
                    // Copy the markup from the response to the container
                    map.modalCapsuleContentContainer.html(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    map.modalCapsuleContentContainer.html('<?= preg_replace('/\r|\n/', '', $this->element('modal_error_content')); ?>');
                }
            });
        };
        // Called when the user's location is successfully determined
        map.capsuleMap.onGeolocationSuccessCallback = function (lat, lng) {
            // Update the button status
            map.activateLocatorButton(map.startLocatorButton);
            // Discover any nearby Capsules
            map.discoverNearbyCapsules(lat, lng);
        };
        // Called if the user does not give permission for their location within a certain period
        map.capsuleMap.onGeolocationPermissionTimeoutCallback = function (isPositionAvailable) {
            if (!isPositionAvailable) {
                // Deactivate the locator
                map.deactivateLocatorButton(map.startLocatorButton);
            }
        };
        // Called whenever the browser tries to determine the user's location
        map.capsuleMap.onGeolocationRequestCallback = function () {
        };
        // Called if there was an error determining the user's location
        map.capsuleMap.onGeolocationErrorCallback = function (errorMessage) {
            // Deactivate the locator
            map.deactivateLocatorButton(map.startLocatorButton);
            // Set the location error messages
            map.setLocationError(errorMessage);
        };

        // Set the initial collection tags from the server
        map.capsuleMap.ctagCapsules = "<?= $ctagCapsules; ?>";
        map.capsuleMap.ctagDiscoveries = "<?= $ctagDiscoveries; ?>";
        // Set the collections from the server
        map.capsuleMap.capsuleCollection = new Collection(<?= $capsules; ?>);
        map.capsuleMap.discoveryCollection = new Collection(<?= $discoveries; ?>);
        // Populate the collections as Markers
        map.capsuleMap.populateCapsuleMarkers(CapsuleType.Capsule, map.capsuleMap.capsuleCollection.objects);
        map.capsuleMap.populateCapsuleMarkers(CapsuleType.Discovery, map.capsuleMap.discoveryCollection.objects);

        // Add a click listener to the locator button that activates or deactivates it
        map.startLocatorButton.on('click', function () {
            // Clear the location error message
            map.clearLocationError();

            if ($(this).hasClass("active")) {
                map.deactivateLocatorButton($(this));
                map.capsuleMap.stopPositionUpdateListener();
            } else {
                map.setLocatorButtonBusy($(this));
                map.capsuleMap.startPositionUpdateListener();
            }
        });

        // Add a click listener for opening the list of recent discoveries
        map.capsulesFoundButton.on('click', function () {
            map.modalCapsuleList.modal('show');
        });

        // Add a click listener to the list items in recent discoveries that focus on the corresponding Markers
        $(document).on('click', '.capsule-list-item', function (e) {
            e.preventDefault();
            // Remove the highlight
            $(this).removeClass("list-group-item-warning");
            // Get the id, lat, lng
            var id = $(this).data('id');
            var lat = parseFloat($(this).data('lat'));
            var lng = parseFloat($(this).data('lng'));
            // Close the modal
            map.modalCapsuleList.modal('hide');
            // Focus on the Marker after a delay
            map.delayedFocusOnLocation(lat, lng, CapsuleType.Discovery, id, 100);
        });

        // If there were was an initial focus point, focus there now
        if (map.initialLat != undefined && map.initialLng != undefined) {
            map.delayedFocusOnLocation(map.initialLat, map.initialLng, map.initialFocusType, map.initialFocusId, 1000);
        }
    });
</script>

<h3><?= __("Find a Capsule"); ?></h3>
<hr>

<div class="row pull-right">
    <div class="col-md-12 text-right">
        <button class="btn btn-default btn-sm" type="button" id="capsules-found-btn">
            <?= __("Capsules Found"); ?> <span class="badge" id="capsules-found-counter">0</span>
        </button>
    </div>
</div>

<h4 class="text-info">
    <span class="glyphicon glyphicon-map-marker"></span>&nbsp;
    <?= __("Capsule Detector 3000"); ?>
</h4>

<hr>

<div class="row">
    <div class="col-md-12 text-center">
        <button id="start-locator-btn" type="button" class="btn btn-warning btn-sm">
            <span class="glyphicon glyphicon-map-marker"></span>
            <?= __("Start searching for Capsules"); ?>
        </button>
        <div class="error-message hidden alert alert-danger" id="location-error-container"></div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12">
        <div id="capsule-locator-map-container">
            <div id="capsule-locator-map"></div>
        </div>
    </div>
</div>

<?= $this->element('modal_capsule'); ?>

<div id="modal-capsule-list" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?= __("Recent Discoveries"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="list-group" id="modal-capsule-list-group">
                    <span id="no-discoveries-message"><?= __("No new discoveries yet, so keep looking!"); ?></span>
                </div>
            </div>
            <div class="modal-footer">
                <small><a href="/discoveries" target="_blank"><?= __("View past discoveries"); ?></a></small>
            </div>
        </div>
    </div>
</div>

<a href="#" id="modal-capsule-list-item" class="hidden list-group-item list-group-item-warning capsule-list-item"
   data-id="0" data-lat="0" data-lng="0"></a>
