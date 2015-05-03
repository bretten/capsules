<?php

App::uses('Component', 'Controller');

/**
 * Contains common API methods that can be used in controllers that rely on
 * Cake's authentication or the StatelessAuthComponent's authentication
 */
class ApiComponent extends Component {

/**
 * Settings
 *
 * @var array
 */
    public $settings = array();

/**
 * Components
 *
 * @var array
 */
    public $components = array('Auth');

/**
 * Request object
 *
 * @var CakeRequest
 */
    public $request;

/**
 * Response object
 *
 * @var CakeResponse
 */
    public $response;

/**
 * Controller
 *
 * @var Controller
 */
    public $controller;

/**
 * Array representing the response body
 *
 * @var array
 */
    public $body;

/**
 * Constructor
 *
 * @param ComponentCollection $collection
 * @param array $settings
 */
    public function __construct(ComponentCollection $collection, $settings = array()) {
        $this->_Collection = $collection;
        $this->settings = array_merge($this->settings, $settings);
        $this->_set($settings);
        if (!empty($this->components)) {
            $this->_componentMap = ComponentCollection::normalizeObjectArray($this->components);
        }
    }

/**
 * Called before the Controller's beforeFilter
 *
 * @param Controller $controller
 * @return void
 */
    public function initialize(Controller $controller) {
        $this->controller = $controller;
        $this->request = $controller->request;
        $this->response = $controller->response;
    }

/**
 * Called after the Controller's beforeFilter but before the Controller action is handled
 *
 * @param Controller $controller
 * @return void
 */
    public function startup(Controller $controller) {
        // Build the body of the response
        $this->body = array(
            'data' => array(),
            'messages' => array()
        );
    }

/**
 * API method to handle registering Users
 *
 * @return string
 */
    public function register() {
        // Make sure it is a POST
        if (!$this->request->is('post')) {
            return $this->responseNotAllowed();
        }

        // Check the request data
        if (!isset($this->request->data['username']) || !isset($this->request->data['email'])
            || !isset($this->request->data['password']) || !isset($this->request->data['confirm_password'])) {
            return $this->responseBadRequest();
        }

        // Create the User's token
        $this->request->data['token'] = Security::hash(uniqid() . 'capsules', null, true);
        // Save the User's token
        if (!$this->controller->User->save($this->request->data, array(
            'confirmPassword' => true,
            'fieldList' => array(
                'username', 'password', 'email', 'confirm_password', 'token'
            )))) {
            // If there were validation errors, then indicate a bad request
            if (!empty($this->controller->User->validationErrors)) {
                // Set the validation messages in the response
                foreach ($this->controller->User->validationErrors as $field => $messages) {
                    foreach ((array)$messages as $message) {
                        $this->body['messages'][] = __($message);
                    }
                }
                // Indicate a bad request
                return $this->responseBadRequest();
            } else {
                // Indicate a server error
                return $this->responseServerError();
            }
        }

        // Add the token to the response
        $this->body['data'] = array(
            'token' => $this->request->data['token']
        );

        // Indicate success
        return $this->responseOk();
    }

/**
 * API method to handle authenticating Users.  Response body contains an authentication token
 * to be used in future API calls.
 *
 * @return string
 */
    public function authenticate() {
        if (!$this->Auth->login()) {
            // Add a message
            $this->body['messages'][] = __("The username or password was incorrect.");
            // Indicate unauthenticated
            return $this->responseUnauthenticated();
        }

        // Create the User's token
        $token = Security::hash(uniqid() . 'capsules' . $this->Auth->user('id'), null, true);
        // Save the User's token
        $data = array(
            'id' => $this->Auth->user('id'),
            'token' => $token
        );
        if (!$this->controller->User->save($data, array('fieldList' => array('id', 'token')))) {
            // Since the token is generated server side, there is no chance for validation errors from a bad request
            // As a result, indicate a server error
            return $this->responseServerError();
        }

        // Add the token to the request body
        $this->body['data'] = array('token' => $token);

        // Indicate success
        return $this->responseOk();
    }

/**
 * API method to return a User's Capsules and Discoveries
 *
 * @return string
 */
    public function points() {
        // Make sure it is a POST request
        if (!$this->request->is('post')) {
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            return $this->responseUnauthenticated();
        }

        // Make sure all the request data is present
        if (!isset($this->request->data['latNE']) || !is_numeric($this->request->data['latNE'])
            || !isset($this->request->data['lngNE']) || !is_numeric($this->request->data['lngNE'])
            || !isset($this->request->data['latSW']) || !is_numeric($this->request->data['latSW'])
            || !isset($this->request->data['lngSW']) || !is_numeric($this->request->data['lngSW'])
        ) {
            return $this->responseBadRequest();
        }

        // Get the User's Capsules
        $capsules = $this->controller->Capsule->getInRectangle(
            $this->request->data['latNE'],
            $this->request->data['lngNE'],
            $this->request->data['latSW'],
            $this->request->data['lngSW'],
            array(
                'conditions' => array(
                    'Capsule.user_id' => $this->Auth->user('id')
                )
            )
        );
        // Add the User's Capsules to the response body
        $this->body['data']['capsules'] = Hash::map($capsules, "{n}.Capsule", function ($data) {
            return array(
                'id' => $data['id'],
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng']
            );
        });
        // Get the User's Discoveries
        $discoveries = $this->controller->Capsule->getDiscovered(
            $this->Auth->user('id'),
            $this->request->data['latNE'],
            $this->request->data['lngNE'],
            $this->request->data['latSW'],
            $this->request->data['lngSW']
        );
        // Add the User's Discoveries to the response
        $this->body['data']['discoveries'] = Hash::map($discoveries, "{n}.Capsule", function ($data) {
            return array(
                'id' => $data['id'],
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng']
            );
        });

        // Indicate a success
        return $this->responseOk();
    }

/**
 * API method to retrieve undiscovered Capsules in the User's radius
 *
 * @return string
 */
    public function ping() {
        // Make sure it is a POST request
        if (!$this->request->is('post')) {
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            return $this->responseUnauthenticated();
        }

        if (!isset($this->request->data['lat']) || !is_numeric($this->request->data['lat'])
            || !isset($this->request->data['lng']) || !is_numeric($this->request->data['lng'])
        ) {
            // Indicate a bad request
            return $this->responseBadRequest();
        }

        // Get the User's undiscovered Capsules
        $capsules = $this->controller->Capsule->getUndiscovered(
            $this->Auth->user('id'),
            $this->request->data['lat'],
            $this->request->data['lng'],
            Configure::read('Map.UserLocation.SearchRadius')
        );
        // Add them to the response
        $this->body['data']['capsules'] = Hash::map($capsules, "{n}.Capsule", function($data) {
            return array(
                'id' => $data['id'],
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng']
            );
        });

        // Indicate success
        return $this->responseOk();
    }

/**
 * Sets the status code to indicate a OK success and sets the body
 *
 * @return string
 */
    private function responseOk() {
        // Indicate a bad request
        $this->response->statusCode(200);
        // Set the body
        return $this->setBody();
    }

/**
 * Sets the status code to indicate a bad request and sets the body
 *
 * @return string
 */
    private function responseBadRequest() {
        // Indicate a bad request
        $this->response->statusCode(400);
        // Set the body
        return $this->setBody();
    }

/**
 * Sets the status code to indicate an unauthenticated request and sets the body
 *
 * @return string
 */
    private function responseUnauthenticated() {
        // Indicate method not allowed if not POST
        $this->response->statusCode(401);
        // Set the body
        return $this->setBody();
    }

/**
 * Sets the status code to indicate that the request type is not allowed and sets the body
 *
 * @return string
 */
    private function responseNotAllowed() {
        // Indicate method not allowed if not POST
        $this->response->statusCode(405);
        // Set the body
        return $this->setBody();
    }

/**
 * Sets the status code to indicate there was a server error and sets the body
 *
 * @return string
 */
    private function responseServerError() {
        // Indicate a server error
        $this->response->statusCode(500);
        // Set the body
        return $this->setBody();
    }

/**
 * Convenience wrapper for setting the JSON body of the response
 *
 * @return string
 */
    private function setBody() {
        return $this->response->body(json_encode($this->body));
    }

}
