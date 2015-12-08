/**
 * Contains functionality that goes with a form for editing Capsules
 *
 * @constructor
 * @author https://github.com/bretten
 */
var CapsuleEditor = function () {
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
