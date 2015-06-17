<?php
App::uses('ModelBehavior', 'Model');

/**
 * Handles file uploads by moving them to the correct location on the file server and providing the proper
 * details to save in the database.
 *
 * @author Brett Namba
 */
class HandleUploadBehavior extends ModelBehavior {

    /**
     * Settings
     *
     * @var array
     */
    public $settings = array(
        'uploadDir' => 'uploads',
        'fields' => array(
            'file' => 'file',
            'location' => 'file_location',
            'publicName' => 'file_public_name',
            'originalName' => 'file_original_name',
            'type' => 'file_type',
            'size' => 'file_size'
        ),
        'allowedTypes' => array(
            'images' => array(
                'image/jpeg', 'image/gif', 'image/png'
            )
        ),
        'maxSize' => array(
            'images' => array(
                'bytes' => 5120000,
                'humanReadable' => '5MB'
            )
        ),
        'currentModel' => 'Model'
    );

    /**
     * Validation errors
     *
     * @var array
     */
    private $validationErrors;

    /**
     * Initiate behavior
     *
     * @param Model $Model Instance of model
     * @param $config array of configuration settings.
     * @return void
     */
    public function setup(Model $Model, $config = array()) {
        $this->settings = array_merge($this->settings, $config);
    }

    /**
     * Gets the upload validation error messages
     *
     * @return array
     */
    public function getUploadValidationMessages() {
        return $this->validationErrors;
    }

    /**
     * @param Model $Model Instance of model
     * @param array $fileUploadData The upload data for the file
     * @return array|bool The processed file data to save on success, otherwise false
     */
    public function handleImageUpload(Model $Model, $fileUploadData) {
        // Clear the validation errors
        $this->validationErrors = array();
        // Check the file upload data for the required keys
        if (!isset($fileUploadData['name']) || !isset($fileUploadData['type']) || !isset($fileUploadData['tmp_name'])
            || !isset($fileUploadData['error']) || !isset($fileUploadData['size'])
        ) {
            $this->addValidationError(__("Please choose a file."));
            return false;
        }

        // Check that the file is actually an image
        // TODO: Below is not 100% guaranteed
        try {
            $imagick = new \Imagick($fileUploadData['tmp_name']);
            $fileType = $imagick->getImageMimeType();
            $fileSize = $imagick->getImageLength();
        } catch (ImagickException $e) {
            $this->addValidationError(__("The image was not valid."));
            return false;
        }

        // Make sure the image type is allowed
        if (!in_array($fileUploadData['type'], $this->settings['allowedTypes']['images'])
            || !in_array($fileType, $this->settings['allowedTypes']['images'])
        ) {
            $this->addValidationError(__("The image type is not supported."));
            return false;
        }

        // Make sure the size does not exceed the limit
        if ($fileUploadData['size'] > $this->settings['maxSize']['images']['bytes']
            || $fileSize > $this->settings['maxSize']['images']['bytes']
            || filesize($fileUploadData['tmp_name']) > $this->settings['maxSize']['images']['bytes']
        ) {
            $this->addValidationError(__("The image cannot exceed "
                . $this->settings['maxSize']['images']['humanReadable']));
            return false;
        }

        // Check for upload errors
        if ($fileUploadData['error'] != UPLOAD_ERR_OK) {
            $this->addValidationError(__("There was a problem uploading the file."));
            return false;
        }

        // Make sure the file is an actual upload
        if (!is_uploaded_file($fileUploadData['tmp_name'])) {
            $this->addValidationError(__("The file is an invalid upload."));
            return false;
        }

        // Generate the new file name
        $fileName = $this->generateFileName();
        // Determine the upload directory
        $fileLocation = APP . $this->settings['uploadDir'];
        // Move the file to the uploads directory
        if (!move_uploaded_file($fileUploadData['tmp_name'], $fileLocation . DS . $fileName)) {
            $this->addValidationError(__("The file could not be properly handled. Please try again."));
            return false;
        }

        // Set the permissions
        if (!chmod($fileLocation . DS . $fileName, 0644)) {
            $this->addValidationError(__("The file could not be properly handled. Please try again."));
            return false;
        }

        // Return the data to be saved to the database
        return array(
            $this->settings['fields']['location'] => $fileLocation,
            $this->settings['fields']['publicName'] => $fileName,
            $this->settings['fields']['originalName'] => $fileUploadData['name'],
            $this->settings['fields']['type'] => $fileType,
            $this->settings['fields']['size'] => $fileSize
        );
    }

    /**
     * Generates a random and unique file name
     *
     * @return string The unique file name
     */
    private function generateFileName() {
        return uniqid(rand(), true);
    }

    /**
     * Adds an upload validation message to the collection
     *
     * @param $message Validation message
     */
    private function addValidationError($message) {
        $this->validationErrors[] = $message;
    }

}
