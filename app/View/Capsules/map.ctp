<script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript" src="/js/CapsuleType.js"></script>
<script type="text/javascript" src="/js/Geolocator.js"></script>
<script type="text/javascript" src="/js/Collection.js"></script>
<script type="text/javascript" src="/js/CapsuleMap.js"></script>
<script type="text/javascript">
    // Define the "namespace"
    var map = {};

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
        // Log that records status updates
        map.capsuleLog = $('#capsule-log');
        // Counter that records how many Capsules have been found in this current session
        map.capsulesFoundCounter = $('#capsules-found-counter');
        // Button that opens the list of recent discoveries
        map.capsulesFoundButton = $('#capsules-found-btn');
        // Button that starts the Capsule locator
        map.startLocatorButton = $('#start-locator-btn');

        // Activates the locator button
        map.activateLocatorButton = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("active btn-success");
            btn.removeClass("btn-warning btn-info");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Searching for Capsules..."); ?>');
            map.logSuccess('<?= __("Capsule Locator 2000 is active!"); ?>');
        };
        // Deactivates the locator button
        map.deactivateLocatorButton = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("btn-warning");
            btn.removeClass("active btn-success btn-info");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Start Searching for Capsules"); ?>');
            map.logInfo('<?= __("Terminated Capsule search."); ?>');
        };
        // Sets the locator button to indicate it is busy
        map.setLocatorButtonBusy = function (btn) {
            if (btn === 'undefined') {
                btn = map.startLocatorButton;
            }
            btn.addClass("btn-info");
            btn.removeClass("active btn-success btn-warning");
            btn.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Starting up..."); ?>');
            map.logInfo('<?= __("Capsule Locator is starting up..."); ?>');
        };
        // Logs a success message
        map.logSuccess = function (message) {
            map.logMessage("text-success", message);
        };
        // Logs a informational message
        map.logInfo = function (message) {
            map.logMessage("text-info", message);
        };
        // Logs a warning message
        map.logWarning = function (message) {
            map.logMessage("text-warning", message);
        };
        // Logs an error message
        map.logError = function (message) {
            map.logMessage("text-danger", message);
        };
        // Logs a message to the Logger with a timestamp
        map.logMessage = function (containerClass, message) {
            // Get the timestamp
            var timestamp = new Date().toLocaleString();
            // Build the markup to append
            var markup = '<div class="' + containerClass + '">[' + timestamp + '] '
                + message + '</div>';
            map.capsuleLog.append(markup);
            // Scroll the logger to the bottom
            map.capsuleLog.scrollTop = map.capsuleLog.scrollHeight;
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
            // Update the log indicating Capsules are being searched for
            map.logWarning('<?= __("Searching for Capsules nearby..."); ?>');
            // Send a request to look for new Capsules
            $.ajax({
                type: 'POST',
                url: '/api/discoveries/',
                data: {'data[lat]': lat, 'data[lng]': lng},
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.status === 200 && data !== 'undefined') {
                        if (data.hasOwnProperty("data") && data.data.hasOwnProperty("capsules")) {
                            // Get the number of Capsules discovered
                            var capsuleCount = Object.keys(data.data.capsules).length;
                            // Update the counter
                            map.capsulesFoundCounter.html(parseInt(map.capsulesFoundCounter.html()) + capsuleCount);
                            // Add the newly discovered Capsules to the map
                            map.capsuleMap.addNewCapsulesFromServer(CapsuleType.Discovery, data.data.capsules);
                            // Update the log
                            map.logSuccess(capsuleCount + ' <?= __("Capsules found") ;?>');
                            // Open the modal
                            map.modalCapsuleList.modal('show');
                            // Append new Capsules to the list of recent discoveries
                            setTimeout(function () {
                                map.addRecentDiscoveries(data.data.capsules);
                            }, 200);
                        }
                    } else if (jqXHR.status === 204) {
                        map.logInfo('<?= __("No new Capsules currently nearby."); ?>');
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

        // Start searching for Capsules
        map.capsuleMap.startPositionUpdateListener();
        // Update the button status to indicate that it is active
        map.setLocatorButtonBusy(map.startLocatorButton);
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
            // Update the log
            map.logSuccess('<?= __("Location updated!"); ?>');
            // Update the button status
            map.activateLocatorButton(map.startLocatorButton);
            // Discover any nearby Capsules
            map.discoverNearbyCapsules(lat, lng);
        };
        // Called if the user does not give permission for their location within a certain period
        map.capsuleMap.onGeolocationPermissionTimeoutCallback = function (isPositionAvailable) {
            if (!isPositionAvailable) {
                // Update the log
                map.logWarning('<?= __("Still awaiting location permission... (Did you deny permission?)"); ?>');
                // Deactivate the locator
                map.deactivateLocatorButton(map.startLocatorButton);
            }
        };
        // Called whenever the browser tries to determine the user's location
        map.capsuleMap.onGeolocationRequestCallback = function () {
            map.logWarning('<?= __("Updating current location..."); ?>');
        };
        // Called if there was an error determining the user's location
        map.capsuleMap.onGeolocationErrorCallback = function (errorMessage) {
            map.logError(errorMessage);
            // Deactivate the locator
            map.deactivateLocatorButton(map.startLocatorButton);
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
            setTimeout(function () {
                // Zoom in on the Marker's location
                map.capsuleMap.zoomMap(17);
                // Center the map on the Marker's location
                map.capsuleMap.centerMap(lat, lng);
                // Animate the Marker after a delay
                setTimeout(function () {
                    map.capsuleMap.animateMarkerById(CapsuleType.Discovery, id, function () {
                        map.capsuleMap.onMarkerClickCallback(id);
                    });
                }, 100);
            }, 100);
        });
    });
</script>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-4 text-left">
                        <button id="start-locator-btn" type="button" class="btn btn-warning btn-sm">
                            <span class="glyphicon glyphicon-map-marker"></span>
                            <?= __("Start searching for Capsules"); ?>
                        </button>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 class="panel-title">
                            <?= __("Capsule Locator 2000"); ?>
                        </h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-default btn-sm" type="button" id="capsules-found-btn">
                            <?= __("Capsules Found"); ?> <span class="badge" id="capsules-found-counter">0</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12" id="capsule-log-container">
                        <samp id="capsule-log">
                        </samp>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <h4 class="modal-title"><?= __("New Capsules discovered!"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="list-group" id="modal-capsule-list-group">
                </div>
            </div>
        </div>
    </div>
</div>

<a href="#" id="modal-capsule-list-item" class="hidden list-group-item list-group-item-warning capsule-list-item"
   data-id="0" data-lat="0" data-lng="0"></a>
