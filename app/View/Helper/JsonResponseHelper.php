<?php

/**
 * Builds JSON responses for View files
 */
class JsonResponseHelper extends AppHelper {

    /**
     * Single message
     *
     * @var string
     */
    private $message = '';

    /**
     * Collection of messages
     *
     * @var array
     */
    private $messages = array();

    /**
     * Data array
     *
     * @var array
     */
    private $data = array();

    /**
     * Builds the response body and encodes it as JSON
     *
     * @return string The JSON response body
     */
    public function getResponseBodyJsonString() {
        $responseBody = array(
            'message' => $this->message ? $this->message : '',
            'messages' => $this->messages && is_array($this->messages) ? $this->messages : array(),
            'data' => $this->data && is_array($this->data) ? $this->data : array(),
        );

        return json_encode($responseBody);
    }

    /**
     * Sets the message
     *
     * @param string $message Single message string
     */
    public function setMessage($message = '') {
        $this->message = $message;
    }

    /**
     * Sets the collection of messages
     *
     * @param array $messages Collection of messages
     */
    public function setMessages($messages = array()) {
        $this->messages = $messages;
    }

    /**
     * Sets the data
     *
     * @param array $data The data array
     */
    public function setData($data = array()) {
        $this->data = $data;
    }

    /**
     * Adds a Capsule to the data array
     *
     * @param array $capsule The Capsule data
     */
    public function addCapsuleToData(array $capsule) {
        // Remove any unnecessary Capsule data
        $capsule = $this->removeUnnecessaryCapsuleData($capsule);

        $this->data['capsule'] = $capsule;
    }

    /**
     * Adds Capsule data to the data array
     *
     * @param array $capsules Collection of Capsule data
     * @param array $requestParams The HTTP request parameters
     */
    public function addCapsulesToData(array $capsules, array $requestParams = array()) {
        // Format the Capsule data
        $capsules = $this->formatCapsuleData($capsules, $requestParams);
        // Remove any unnecessary Capsule data
        $capsules = $this->removeUnnecessaryCapsuleData($capsules, /* isCollection */
            true);

        $this->data['capsules'] = $capsules;
    }

    /**
     * Adds an authentication token to the data array
     *
     * @param string $token Authentication token
     */
    public function addAuthTokenToData($token = "") {
        $this->data['token'] = $token;
    }

    /**
     * Adds the collection tags to the data array
     *
     * @param string $ctag User's ctag data
     */
    public function addCtagToData($ctag = "") {
        $this->data['ctag'] = $ctag;
    }

    /**
     * Formats the JSON output format for the Capsules depending on the request parameters
     *
     * @param array $capsules The collection of Capsules
     * @param array $requestParams The HTTP request parameters
     * @return array The formatted Capsule data
     */
    private function formatCapsuleData(array $capsules, array $requestParams = array()) {
        if (empty($requestParams)) {
            return $capsules;
        }

        // Format the Capsules as an object where the object properties will be the Capsule IDs
        $capsules = $this->formatCapsulesAsObject($capsules, $requestParams);

        return $capsules;
    }

    /**
     * Sets the Capsule IDs as keys in the Capsule collection data array.  When this array is encoded to JSON,
     * the collection of Capsules will become a JSON object instead of a JSON array, where the object properties
     * are the Capsule IDs.
     *
     * @param array $capsules The collection of Capsules
     * @param array $requestParams The HTTP request parameters
     * @return array The formatted Capsule data
     */
    private function formatCapsulesAsObject(array $capsules, array $requestParams) {
        // Check to see if the request parameters indicate to format the Capsules as a JSON object
        if (isset($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_CAPSULES_AS_OBJECT])
            && $requestParams[\Capsules\Http\RequestContract::PARAM_NAME_CAPSULES_AS_OBJECT] == "1"
        ) {
            $capsules = Hash::combine($capsules, "{n}.Capsule.id", "{n}");
        }
        return $capsules;
    }

    /**
     * Removes unnecessary Capsule-related data from the specified data array
     *
     * @param array $data The array to remove unnecessary data from
     * @param bool|false $isCollection Whether or not the data array contains a collection of Capsules
     * @return array The modified data array
     */
    private function removeUnnecessaryCapsuleData(array $data, $isCollection = false) {
        // Determine the path prefixes based on whether or not the data is a collection
        $capsulePathPrefix = $isCollection ? "{n}.Capsule" : "Capsule";
        $memoirPathPrefix = $isCollection ? "{n}.Memoir" : "Memoir.{n}";
        // Remove the User ID
        $data = Hash::remove($data, $capsulePathPrefix . ".user_id");
        // Remove Memoir information
        $data = Hash::remove($data, $memoirPathPrefix . ".file_location");
        $data = Hash::remove($data, $memoirPathPrefix . ".file_public_name");
        $data = Hash::remove($data, $memoirPathPrefix . ".file_type");
        $data = Hash::remove($data, $memoirPathPrefix . ".file_size");
        $data = Hash::remove($data, $memoirPathPrefix . ".modified");

        return $data;
    }

}
