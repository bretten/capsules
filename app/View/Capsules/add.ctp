<script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUnWgq9_H4JNhOPHhp1yPO2AnEf-aXPhE"></script>
<script type="text/javascript" src="/js/Geolocator.js"></script>
<script type="text/javascript" src="/js/CapsuleEditor.js"></script>
<script type="text/javascript" src="/js/CapsuleMap.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // Define the namespace
        var capsuleEditor = {};

        // General error notification
        capsuleEditor.errorNotification = $('#flashMessage');
        // Displays location-related errors
        capsuleEditor.geolocationRequestErrorContainer = $("#CapsuleLocationError");
        // Input that triggers geolocation
        capsuleEditor.locationRequestInput = $('#location-request-btn');
        // Loading indicator for when an upload file is chosen
        capsuleEditor.memoirLoadingIndicator = $('#memoir-loading-indicator');
        // Container for displaying file upload previews
        capsuleEditor.uploadPreviewContainer = $('#upload-preview-container');
        // Form
        capsuleEditor.capsuleForm = $('#CapsuleAddForm');
        // Submit button
        capsuleEditor.submitButton = capsuleEditor.capsuleForm.find('button[type="submit"]');
        // Submit loading indicator
        capsuleEditor.submitLoadingIndicator = capsuleEditor.capsuleForm.find("#submit-loading-indicator");
        // Capsule latitude input
        capsuleEditor.capsuleLatInput = capsuleEditor.capsuleForm.find("#CapsuleLat");
        // Capsule longitude input
        capsuleEditor.capsuleLngInput = capsuleEditor.capsuleForm.find("#CapsuleLng");
        // Capsule name input
        capsuleEditor.capsuleNameInput = capsuleEditor.capsuleForm.find('#CapsuleName');
        // Memoir title input
        capsuleEditor.memoirTitleInput = capsuleEditor.capsuleForm.find('#Memoir0Title');
        // Memoir file input
        capsuleEditor.memoirFileInput = capsuleEditor.capsuleForm.find('#Memoir0File');
        // Capsule name error container
        capsuleEditor.capsuleNameErrorContainer = capsuleEditor.capsuleForm.find("#CapsuleNameError");
        // Memoir title error container
        capsuleEditor.memoirTitleErrorContainer = capsuleEditor.capsuleForm.find("#Memoir0TitleError");
        // Memoir file error container
        capsuleEditor.memoirFileErrorContainer = capsuleEditor.capsuleForm.find("#Memoir0FileError");
        // All error message containers
        capsuleEditor.errorMessageContainers = capsuleEditor.capsuleForm.find(".error-message");


        // Called when the user's location is updated
        capsuleEditor.onLocationUpdate = function () {
            capsuleEditor.clearErrorMessages(capsuleEditor.geolocationRequestErrorContainer);
            capsuleEditor.locationRequestInput.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Location retrieved!"); ?>');
            capsuleEditor.locationRequestInput.removeClass();
            capsuleEditor.locationRequestInput.addClass("btn btn-success");
        };
        // Called while the user's location is trying to be found
        capsuleEditor.onLocationInProgress = function () {
            capsuleEditor.clearErrorMessages(capsuleEditor.geolocationRequestErrorContainer);
            capsuleEditor.locationRequestInput.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Getting location..."); ?>');
            capsuleEditor.locationRequestInput.removeClass();
            capsuleEditor.locationRequestInput.addClass("btn btn-info");
        };
        // Called if there was an error finding the user's location
        capsuleEditor.onLocationError = function () {
            capsuleEditor.locationRequestInput.html('<span class="glyphicon glyphicon-map-marker"></span> <?= __("Location could not be retrieved!"); ?>');
            capsuleEditor.locationRequestInput.removeClass();
            capsuleEditor.locationRequestInput.addClass("btn btn-danger");
        };
        // Sets the error message for location errors
        capsuleEditor.setLocationErrorMessage = function (errorMessage) {
            capsuleEditor.setErrorMessages(capsuleEditor.geolocationRequestErrorContainer, errorMessage);
        };
        // Sets the markup on an error container and then makes it visible
        capsuleEditor.setErrorMessages = function (container, markup) {
            container.html(markup);
            container.removeClass('hidden');
        };
        // Hides an error container and removes the markup
        capsuleEditor.clearErrorMessages = function (container) {
            container.addClass('hidden');
            container.html("");
        };
        // Executed before the form submit
        capsuleEditor.beforeFormSubmit = function () {
            // Disable the submit button
            capsuleEditor.submitButton.prop('disabled', true);
            // Show a busy indicator
            capsuleEditor.submitLoadingIndicator.removeClass('hidden');
        };
        // Executed after the form submit
        capsuleEditor.afterFormSubmit = function () {
            // Enable the submit button
            capsuleEditor.submitButton.prop('disabled', false);
            // Hide the busy indicator
            capsuleEditor.submitLoadingIndicator.addClass('hidden');
        };
        // Sends a POST request to validate the Capsule
        capsuleEditor.sendValidationRequest = function (form) {
            $.ajax({
                type: 'POST',
                url: "/api/capsule?validate=true",
                data: form.find('input').not(':file').serialize(),
                dataType: 'json',
                beforeSend: function (jqXHR, settings) {
                    capsuleEditor.beforeFormSubmit();
                },
                complete: function (jqXHR, textStatus) {
                    capsuleEditor.afterFormSubmit();
                },
                success: function (data, textStatus, jqXHR) {
                    capsuleEditor.sendSaveRequest(form);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    capsuleEditor.parseServerSideValidation(jqXHR, textStatus, errorThrown);
                }
            });
        };
        // Sends a POST request to save the Capsule
        capsuleEditor.sendSaveRequest = function (form) {
            $.ajax({
                type: 'POST',
                url: "/api/capsule?validate=false",
                data: new FormData(form[0]),
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function (jqXHR, settings) {
                    capsuleEditor.beforeFormSubmit();
                },
                complete: function (jqXHR, textStatus) {
                    capsuleEditor.afterFormSubmit();
                },
                success: function (data, textStatus, jqXHR) {
                    window.location.replace("/capsules");
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    capsuleEditor.parseServerSideValidation(jqXHR, textStatus, errorThrown);
                }
            });
        };
        // Parses server-side validation
        capsuleEditor.parseServerSideValidation = function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.hasOwnProperty('responseJSON')) {
                var json = jqXHR.responseJSON;
                if (json.hasOwnProperty('messages')) {
                    // Display the Capsule error messages
                    if (json.messages.hasOwnProperty('name') && $.isArray(json.messages.name)) {
                        capsuleEditor.setErrorMessages(capsuleEditor.capsuleNameErrorContainer,
                            json.messages.name.join("<br>"));
                    }
                    // Display the Memoir error messages
                    if (json.messages.hasOwnProperty('Memoir')) {
                        // Get the Memoir error messages
                        var memoirMessages = json.messages.Memoir;
                        if (memoirMessages.length > 0) {
                            $.each(memoirMessages, function (index, messages) {
                                // Memoir container
                                var memoirContainer = capsuleEditor.capsuleForm.find('div.memoir[data-id="' + index + '"]');
                                // Title error
                                if (messages.hasOwnProperty('title')) {
                                    var titleErrorContainer = memoirContainer.find('#Memoir' + index + 'TitleError');
                                    capsuleEditor.setErrorMessages(titleErrorContainer, messages.title.join("<br>"));
                                }
                                // Message error
                                if (messages.hasOwnProperty('message')) {
                                    var messageErrorContainer = memoirContainer.find('#Memoir' + index + 'MessageError');
                                    capsuleEditor.setErrorMessages(messageErrorContainer,
                                        messages.message.join("<br>"));
                                }
                                // File error
                                if (messages.hasOwnProperty('file')) {
                                    var fileErrorContainer = memoirContainer.find('#Memoir' + index + 'FileError');
                                    capsuleEditor.setErrorMessages(fileErrorContainer, messages.file.join("<br>"));
                                }
                            });
                        }
                    }
                }
            }
        };

        // Initialize the editor
        capsuleEditor.editor = new CapsuleEditor();
        // Initialize the map
        capsuleEditor.map = new CapsuleMap();
        // Initialize the map
        capsuleEditor.map.initializeMap(document.getElementById("capsule-editor-map"));

        // Max file size
        capsuleEditor.editor.maxUploadFileSize = <?= Configure::read('Upload.Limit.Image'); ?>;
        // Validation message for reaching the max size of file uploads
        capsuleEditor.editor.errorMessageMaxUploadLimit = "<?= __("The file size cannot exceed 5MB"); ?>";
        // Validation message for not choosing a file
        capsuleEditor.editor.errorMessageChooseFile = "<?= __("Please choose a file."); ?>";
        // Validation message for empty Capsule name
        capsuleEditor.editor.errorMessageCapsuleNameEmpty = "<?= __("Please enter a name."); ?>";
        // Validation message for Capsule name exceeding limit
        capsuleEditor.editor.errorMessageCapsuleNameExceedsLimit =
            "<?= __("The name cannot exceed 255 characters."); ?>";
        // Validation message for empty Memoir title
        capsuleEditor.editor.errorMessageMemoirTitleEmpty = "<?= __("Please enter a title."); ?>";
        // Validation message for Memoir title exceeding limit
        capsuleEditor.editor.errorMessageMemoirTitleExceedsLimit =
            "<?= __("The title cannot exceed 255 characters."); ?>";

        // Called when a file is chosen to be uploaded
        capsuleEditor.editor.onChooseFileCallback = function () {
            capsuleEditor.memoirLoadingIndicator.removeClass("hidden");
        };
        // Called when a file has been loaded by the browser
        capsuleEditor.editor.onFileReady = function (e) {
            capsuleEditor.uploadPreviewContainer.attr('src', e.target.result);
            capsuleEditor.uploadPreviewContainer.removeClass("hidden");
            capsuleEditor.memoirLoadingIndicator.addClass("hidden");
        };

        // Called when the map successfully determines the user's location
        capsuleEditor.map.onGeolocationSuccessCallback = function () {
            capsuleEditor.onLocationUpdate();
        };
        // Called if the user does not give permission for their location within a certain period
        capsuleEditor.map.onGeolocationPermissionTimeoutCallback = function (isPositionAvailable) {
            if (isPositionAvailable) {
                capsuleEditor.onLocationUpdate();
            } else {
                capsuleEditor.onLocationError();
            }
        };
        // Called whenever the browser tries to determine the user's location
        capsuleEditor.map.onGeolocationRequestCallback = function () {
            capsuleEditor.onLocationInProgress();
        };
        // Called if there was an error determining the user's location
        capsuleEditor.map.onGeolocationErrorCallback = function (errorMessage) {
            capsuleEditor.onLocationError();
            capsuleEditor.setLocationErrorMessage(errorMessage);
        };

        // Add a listener to the location request button for getting the current location
        capsuleEditor.locationRequestInput.on('click', function () {
            capsuleEditor.map.requestPosition();
        });
        // Add a listener whenever a file input is changed
        capsuleEditor.memoirFileInput.on('change', function () {
            // Clear the error container
            capsuleEditor.clearErrorMessages(capsuleEditor.memoirFileErrorContainer);
            // Show the preview
            capsuleEditor.editor.onFileInputChange($(this));
        });
        // Add a listener for the form submit
        capsuleEditor.capsuleForm.on('submit', function (e) {
            e.preventDefault();
            // Clear any previous errors
            capsuleEditor.errorNotification.addClass('hidden');
            capsuleEditor.clearErrorMessages(capsuleEditor.errorMessageContainers);
            // Set the position inputs
            capsuleEditor.capsuleLatInput.val(capsuleEditor.map.geolocator.lat);
            capsuleEditor.capsuleLngInput.val(capsuleEditor.map.geolocator.lng);
            // Validate Capsule inputs on client-side
            var capsuleErrors = capsuleEditor.editor.validateCapsule(capsuleEditor.capsuleNameInput.val(),
                capsuleEditor.capsuleLatInput.val(), capsuleEditor.capsuleLngInput.val());
            var capsuleNameErrors = capsuleErrors.nameErrors;
            var capsuleLocationErrors = capsuleErrors.locationErrors;
            // Validate Memoir inputs on client-side
            var memoirErrors = capsuleEditor.editor.validateMemoir(capsuleEditor.memoirTitleInput.val());
            var memoirTitleErrors = memoirErrors.titleErrors;
            // Validate file inputs on client-side
            var memoirFileErrors = capsuleEditor.editor.validateFileInput(capsuleEditor.memoirFileInput);
            // Check if client-side validation was successful
            if (capsuleNameErrors.length < 1 && capsuleLocationErrors.length < 1 && memoirTitleErrors.length < 1 && memoirFileErrors.length < 1) {
                // Validate the form on the server-side
                capsuleEditor.sendValidationRequest(capsuleEditor.capsuleForm);
            } else {
                // Display any errors
                if (capsuleNameErrors.length > 0) {
                    capsuleEditor.setErrorMessages(capsuleEditor.capsuleNameErrorContainer,
                        capsuleNameErrors.join("<br>"));
                }
                if (capsuleLocationErrors.length > 0) {
                    capsuleEditor.setErrorMessages(capsuleEditor.geolocationRequestErrorContainer,
                        capsuleLocationErrors.join("<br>"));
                }
                if (memoirTitleErrors.length > 0) {
                    capsuleEditor.setErrorMessages(capsuleEditor.memoirTitleErrorContainer,
                        memoirTitleErrors.join("<br>"));
                }
                if (memoirFileErrors.length > 0) {
                    capsuleEditor.setErrorMessages(capsuleEditor.memoirFileErrorContainer,
                        memoirFileErrors.join("<br>"));
                }
                // Show the general error notification
                capsuleEditor.errorNotification.removeClass('hidden');
                // Scroll to the top
                $('html, body').animate({scrollTop: 0}, 300);
            }
        });

        // Prompt the user for the current position when the page is ready
        capsuleEditor.map.requestPosition();
    });
</script>


<?php
echo $this->Form->create('Capsule',
    array('id' => 'CapsuleAddForm', 'role' => 'form', 'type' => 'file', 'novalidate' => 'novalidate'));
echo $this->Form->input('lat', array('type' => 'hidden'));
echo $this->Form->input('lng', array('type' => 'hidden'));
?>

<h3><?= __("Bury a Capsule"); ?></h3>
<hr>

<div class="row">
    <div class="col-md-12">

        <div class="row">
            <div class="col-md-12">
                <?= $this->element('notification', array(
                    'message' => __("Please fix the errors below."),
                    'class' => 'alert-danger hidden'
                )); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h2 class="text-center text-success">
                    <span class="glyphicon glyphicon-comment"></span>&nbsp;
                    <?= __("What will you call it?"); ?>
                </h2>
            </div>
            <div class="col-md-6">
                <h4 class="text-info">
                    <span class="glyphicon glyphicon-pencil"></span>&nbsp;
                    <?= __("Name the Capsule"); ?>
                </h4>
                <?php
                echo $this->Form->input('name', array(
                    'div' => 'form-group', 'class' => 'form-control', 'label' => false,
                    'after' => '<div class="error-message hidden alert alert-danger" id="CapsuleNameError"></div>'
                ));
                ?>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <h2 class="text-center text-success">
                    <span class="glyphicon glyphicon-globe"></span>&nbsp;
                    <?= __("Where will you put it?"); ?>
                </h2>

                <div class="row">
                    <div class="col-md-12">
                        <div id="capsule-editor-map-container">
                            <div id="capsule-editor-map"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="text-info">
                    <span class="glyphicon glyphicon-map-marker"></span>&nbsp;
                    <?= __("Detect your location"); ?>
                </h4>
                <hr>
                <div id="listen" class="text-center">
                    <button id="location-request-btn" type="button" class="btn btn-info">
                        <span class="glyphicon glyphicon-map-marker"></span>
                        <?= __("Get My Location"); ?>
                    </button>
                    <div class="error-message hidden alert alert-danger" id="CapsuleLocationError"></div>
                </div>
                <hr>
                <div class="well">
                    <strong><?= __("NOTE:"); ?></strong>&nbsp;
                    <?=
                    __("If you are using a mobile device, then your current position can usually be accurately
                    determined using the device's location services.  If you are using a desktop or laptop, then
                    the location is determined by your browser and will not always be accurate.");
                    ?>
                </div>
            </div>
        </div>

        <hr>

        <div class="row memoir" data-id="0">
            <div class="col-md-6">
                <h2 class="text-center text-success">
                    <span class="glyphicon glyphicon-picture"></span>&nbsp;
                    <?= __("What will you put inside?"); ?>
                </h2>

                <div class="row">
                    <div class="col-md-offset-2 col-md-8">
                        <?= $this->element('loading_indicator', array('id' => 'memoir-loading-indicator')); ?>
                        <img src="" class="img-responsive img-thumbnail hidden" id="upload-preview-container">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="text-info">
                    <span class="glyphicon glyphicon-paperclip"></span>&nbsp;
                    <?= __("Choose a picture"); ?>
                </h4>
                <?php
                echo $this->Form->input('Memoir.0.file', array(
                    'div' => 'form-group', 'class' => 'memoir-file', 'label' => false,
                    'type' => 'file', 'accepts' => 'image/png, image/jpeg, image/gif',
                    'after' => '<div class="error-message hidden alert alert-danger" id="Memoir0FileError"></div>'
                ));
                ?>
                <hr>
                <h4 class="text-info">
                    <span class="glyphicon glyphicon-pencil"></span>&nbsp;
                    <?= __("Name this picture"); ?>
                </h4>
                <?php
                echo $this->Form->input('Memoir.0.title', array(
                    'div' => 'form-group', 'class' => 'form-control', 'label' => false,
                    'after' => '<div class="error-message hidden alert alert-danger" id="Memoir0TitleError"></div>'
                ));
                ?>
                <hr>
                <h4 class="text-info">
                    <span class="glyphicon glyphicon-book"></span>&nbsp;
                    <?= __("Leave an accompanying message"); ?>
                </h4>
                <?php
                echo $this->Form->input('Memoir.0.message', array(
                    'div' => 'form-group', 'class' => 'form-control', 'label' => false,
                    'after' => '<div class="error-message hidden alert alert-danger" id="Memoir0MessageError"></div>'
                ));
                ?>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="text-center text-success">
                    <span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;
                    <?= __("Is it ready?"); ?>
                </h2>
                <button type="submit" class="btn btn-lg btn-success"><?= __("Bury that Capsule!"); ?></button>
                <div class="row">
                    <?= $this->element('loading_indicator', array('id' => 'submit-loading-indicator')); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Form->end(); ?>
