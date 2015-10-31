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
        $this->data['capsule'] = $capsule;
    }

    /**
     * Adds Capsule data to the data array
     *
     * @param array $capsules Collection of Capsule data
     */
    public function addCapsulesToData(array $capsules) {
        $this->data['capsules'] = $capsules;
    }

    /**
     * Adds an authentication token to the data array
     *
     * @param array $token Authentication token
     */
    public function addAuthTokenToData(array $token) {
        $this->data['token'] = $token;
    }

}
