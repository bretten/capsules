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
            || !isset($this->request->data['password']) || !isset($this->request->data['confirm_password'])
        ) {
            return $this->responseBadRequest();
        }

        // Create the User's token
        $this->request->data['token'] = Security::hash(uniqid() . 'capsules', null, true);
        // Save the User's token
        if (!$this->controller->User->save($this->request->data, array(
            'confirmPassword' => true,
            'fieldList' => array(
                'username', 'password', 'email', 'confirm_password', 'token'
            )))
        ) {
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
            return ApiComponent::buildCapsule($data);
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
            return ApiComponent::buildCapsule($data);
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
        $this->body['data']['capsules'] = Hash::map($capsules, "{n}.Capsule", function ($data) {
            return ApiComponent::buildCapsule($data);
        });

        // Indicate success
        return $this->responseOk();
    }

    /**
     * API method to open a Capsule
     *
     * @return string
     */
    public function open() {
        // Make sure it is a POST request
        if (!$this->request->is('post')) {
            // Indicate non-POST's are not allowed
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Make sure all the request data is present
        if (!isset($this->request->data['id'])
            || !isset($this->request->data['lat']) || !is_numeric($this->request->data['lat'])
            || !isset($this->request->data['lng']) || !is_numeric($this->request->data['lng'])
        ) {
            // Indicate a bad request
            return $this->responseBadRequest();
        }

        // Make sure the Capsule to be opened exists
        if (!$this->controller->Capsule->exists($this->request->data['id'])) {
            // Indicate resource not found
            return $this->responseNotFound();
        }
        // Get the Capsule
        $capsule = $this->controller->Capsule->findById($this->request->data['id']);

        // See if the User has already discovered the Capsule
        if ($discovery = $this->controller->Capsule->Discovery->created(
            $this->request->data['id'],
            $this->Auth->user('id'))
        ) {
            // Return the existing Discovery
            $this->body['data']['discovery'] = ApiComponent::buildDiscovery($capsule['Capsule'], $discovery['Discovery']);
        } else {
            // Make sure the User is close enough to the Capsule
            if ($this->controller->Capsule->isReachable(
                $this->request->data['id'],
                $this->request->data['lat'],
                $this->request->data['lng'],
                Configure::read('Map.UserLocation.DiscoveryRadius'))
            ) {
                // Attempt to save the Discovery
                if ($insert = $this->controller->Capsule->Discovery->saveNew(
                    $this->request->data['id'],
                    $this->Auth->user('id')
                )
                ) {
                    $this->body['data']['discovery'] = ApiComponent::buildDiscovery($capsule['Capsule'], $insert['Discovery']);
                } else {
                    // There was a server error when trying to save the Discovery
                    return $this->responseServerError();
                }
            } else {
                // The User was too far away
                return $this->responseForbidden();
            }
        }

        // Indicate success
        return $this->responseOk();
    }

    /**
     * API method to get the ctag for the specified collection
     *
     * @param string $collection Collection to get the ctag for
     * @return string
     */
    public function ctag($collection = null) {
        // Make sure it is a GET request
        if (!$this->request->is('get')) {
            // Indicate that the request method is not allowed
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Make sure the collection was specified
        if (!$collection) {
            // Indicate a bad request
            return $this->responseBadRequest();
        }

        // Get the ctag based on which collection
        $ctag = "";
        switch ($collection) {
            case "capsules":
                $ctag = $this->controller->User->field('ctag_capsules', array('User.id' => $this->Auth->user('id')));
                break;
            case "discoveries":
                $ctag = $this->controller->User->field('ctag_discoveries', array('User.id' => $this->Auth->user('id')));
                break;
            default:
                // Unrecognized collection, so indicate a bad request
                return $this->responseBadRequest();
                break;
        }

        // Add the ctag to the response body
        $this->body['data']['ctag'] = $ctag;

        // Indicate success
        return $this->responseOk();
    }

    /**
     * API method to retrieve the entity status on the specified collection
     *
     * @param string $collection The collection to get the entity status on
     * @return string
     */
    public function status($collection = null) {
        // Make sure it is a GET request
        if (!$this->request->is('get')) {
            // Indicate that the request method is not allowed
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Make sure the collection was specified
        if (!$collection) {
            // Indicate a bad request
            return $this->responseBadRequest();
        }

        // Determine which collection to get status on
        switch ($collection) {
            case "capsules":
                $capsules = $this->controller->Capsule->find('all', array(
                    'conditions' => array(
                        'Capsule.user_id' => $this->Auth->user('id')
                    ),
                    'fields' => array('Capsule.id', 'Capsule.etag')
                ));
                // Add them to the response
                $this->body['data']['capsules'] = Hash::map($capsules, "{n}.Capsule", function ($data) {
                    return ApiComponent::buildCapsule($data);
                });
                break;
            case "discoveries":
                $discoveries = $this->controller->Capsule->find('all', array(
                    'joins' => array(
                        array(
                            'table' => 'discoveries',
                            'alias' => 'Discovery',
                            'type' => 'INNER',
                            'conditions' => array(
                                'Capsule.id = Discovery.capsule_id',
                                'Discovery.user_id' => $this->Auth->user('id')
                            )
                        )
                    ),
                    'fields' => array('Capsule.id', 'Discovery.etag')
                ));
                // Add them to the response
                $this->body['data']['discoveries'] = Hash::map($discoveries, "{n}", function ($data) {
                    return ApiComponent::buildDiscovery($data['Capsule'], $data['Discovery']);
                });
                break;
            default:
                // Unrecognized collection, so indicate a bad request
                return $this->responseBadRequest();
                break;
        }

        // Indicate success
        return $this->responseOk();
    }

    /**
     * API method to report on the specified collection and ids
     *
     * @param string $collection The collection to report on
     * @return string
     */
    public function report($collection = null) {
        // Make sure it is a GET request
        if (!$this->request->is('post')) {
            // Indicate that the request method is not allowed
            return $this->responseNotAllowed();
        }

        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Make sure the collection was specified
        if (!$collection || !isset($this->request->data['id'])) {
            // Indicate a bad request
            return $this->responseBadRequest();
        }

        // Determine which collection to report on
        switch ($collection) {
            case "capsules":
                $capsules = $this->controller->Capsule->find('all', array(
                    'conditions' => array(
                        'Capsule.id' => $this->request->data['id'],
                        'Capsule.user_id' => $this->Auth->user('id')
                    ),
                    'fields' => array('Capsule.id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng', 'Capsule.etag')
                ));
                // Add them to the response
                $this->body['data']['capsules'] = Hash::map($capsules, "{n}.Capsule", function ($data) {
                    return ApiComponent::buildCapsule($data);
                });
                break;
            case "discoveries":
                $discoveries = $this->controller->Capsule->find('all', array(
                    'conditions' => array(
                        'Capsule.id' => $this->request->data['id'],
                        'Discovery.user_id' => $this->Auth->user('id')
                    ),
                    'joins' => array(
                        array(
                            'table' => 'discoveries',
                            'alias' => 'Discovery',
                            'type' => 'INNER',
                            'conditions' => array(
                                'Capsule.id = Discovery.capsule_id',
                                'Discovery.user_id' => $this->Auth->user('id')
                            )
                        )
                    ),
                    'fields' => array(
                        'Capsule.id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng',
                        'Discovery.favorite', 'Discovery.rating', 'Discovery.etag'
                    )
                ));
                // Add them to the response
                $this->body['data']['discoveries'] = Hash::map($discoveries, "{n}", function ($data) {
                    return ApiComponent::buildDiscovery($data['Capsule'], $data['Discovery']);
                });
                break;
            default:
                // Unrecognized collection, so indicate a bad request
                return $this->responseBadRequest();
                break;
        }

        // Indicate success
        return $this->responseOk();
    }

    /**
     * API method to perform various actions on a single Capsule
     *
     * @param int $id
     * @return string
     */
    public function capsule($id = null) {
        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Vary the action based on the HTTP method
        if ($this->request->is('post')) {
            // TODO Decide if editing should be allowed
            if ($id) {
                return $this->responseNotAllowed();
            }

            // Handle some Capsule data here to prevent form tampering
            $fieldList = array(
                'Capsule' => array(
                    'name',
                    'point',
                    'user_id',
                    'etag'
                )
            );
            if ($id) {
                $this->request->data['Capsule']['id'] = $id;
                unset($this->request->data['Capsule']['lat']);
                unset($this->request->data['Capsule']['lng']);
                // Make sure the Capsule exists and that it belongs to the User
                if (!$this->controller->Capsule->exists($id)
                    || !$this->controller->Capsule->ownedBy($this->Auth->user('id'), $id)
                ) {
                    // Indicate the Capsule was not found
                    return $this->responseNotFound();
                }
            } else {
                $fieldList = array_merge_recursive($fieldList, array(
                    'Capsule' => array(
                        'lat', 'lng'
                    )
                ));
            }

            // Validate the text form inputs
            if (!isset($this->request->query['validate']) || $this->request->query['validate'] != 'false') {
                // Validate only the Memoir text fields
                $validateOnlyList = array_merge($fieldList, array(
                    'Memoir' => array('title')
                ));
                // Validate
                if ($this->controller->Capsule->saveAll($this->request->data, array(
                    'deep' => true, 'fieldList' => $validateOnlyList, 'validate' => 'only'
                ))
                ) {
                    // Indicate no content
                    return $this->responseNoContent();
                } else {
                    // Build the response body
                    $this->body['messages'] = $this->controller->Capsule->validationErrors;
                    // Indicate a bad request
                    return $this->responseBadRequest();
                }
            }

            // Save the Capsule
            if ($this->controller->Capsule->saveAllWithUploads($this->request->data, array(
                'deep' => true, 'fieldList' => $fieldList, 'associateOwner' => true,
                'updateCtagForUser' => $this->Auth->user('id')
            ))
            ) {
                // Determine the ID of the INSERTed/UPDATEd Capsule
                if ($id) {
                    $capsuleId = $id;
                } else {
                    $capsuleId = $this->controller->Capsule->getLastInsertID();
                }

                // Get the Capsule
                $capsule = $this->controller->Capsule->find('first', array(
                    'conditions' => array(
                        'Capsule.id' => $capsuleId,
                        'Capsule.user_id' => $this->Auth->user('id')
                    ),
                    'fields' => array('Capsule.id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng', 'Capsule.etag')
                ));

                // Build the response body
                $this->body['data'] = array(
                    'capsule' => array(
                        'isNew' => ($id) ? false : true,
                        'id' => $capsule['Capsule']['id'],
                        'lat' => $capsule['Capsule']['lat'],
                        'lng' => $capsule['Capsule']['lng'],
                        'name' => $capsule['Capsule']['name']
                    )
                );
                // Indicate a success
                return $this->responseOk();
            } else {
                // Build the response body
                $this->body['messages'] = $this->controller->Capsule->validationErrors;
                // Indicate a bad request
                return $this->responseBadRequest();
            }

            // If the POST request made it here, then something went wrong
            return $this->responseServerError();
        } else if ($this->request->is('delete')) {
            // DELETE a Capsule
            $this->controller->Capsule->id = $id;
            // Make sure the Capsule exists and belongs to the User
            if (!$this->controller->Capsule->exists() || !$this->controller->Capsule->ownedBy($this->Auth->user('id'))) {
                // Indicate a success in case the Capsule was already deleted on the server
                return $this->responseNoContent();
            }
            // Delete
            if ($this->controller->Capsule->delete()) {
                // Indicate a success (no response body)
                return $this->responseNoContent();
            } else {
                // Indicate a server error
                return $this->responseServerError();
            }
        } else {
            // The HTTP method is not allowed, so indicate that in the response
            return $this->responseNotAllowed();
        }
    }

    /**
     * API method to perform various actions on a single Discovery
     *
     * @param int $id
     * @return string
     */
    public function discovery($id = null) {
        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            // Indicate that the request was unauthenticated
            return $this->responseUnauthenticated();
        }

        // Vary the action based on the HTTP method
        if ($this->request->is('post')) {
            // Make sure an ID is specified
            if (!$id) {
                // Indicate a bad request
                return $this->responseBadRequest();
            }
            // Make sure the User has discovered this Capsule
            if (!$exists = $this->controller->Discovery->created($id, $this->Auth->user('id'))) {
                // Indicate the resource was not found
                return $this->responseNotFound();
            }
            // UPDATE the Discovery
            $this->request->data['id'] = $exists['Discovery']['id'];
            if ($result = $this->controller->Discovery->save($this->request->data, array(
                'updateCtagForUser' => $this->Auth->user('id')
            ))
            ) {
                // Add the updated Discovery to the body
                $this->body['data']['discovery'] = ApiComponent::buildDiscovery(array('id' => $id), $result['Discovery']);
                // Indicate a success
                return $this->responseOk();
            }

            // If the POST request made it here, then something went wrong
            return $this->responseServerError();
        } else {
            // The HTTP method is not allowed, so indicate that in the response
            return $this->responseNotAllowed();
        }
    }

    /**
     * Sets the status code to indicate a OK success and sets the body
     *
     * @return string
     */
    private function responseOk() {
        // Indicate success
        $this->response->statusCode(200);
        // Set the body
        return $this->setBody();
    }

    /**
     * Sets the status code to indicate there is no body content
     *
     * @return string
     */
    private function responseNoContent() {
        // Indicate that there is no content
        $this->response->statusCode(204);
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
     * Sets the status code to indicate that the user is unauthorized
     *
     * @return string
     */
    private function responseForbidden() {
        // Indicate the user is unauthorized
        $this->response->statusCode(403);
        // Set the body
        return $this->setBody();
    }

    /**
     * Sets the status code to indicate a resource was not found
     *
     * @return string
     */
    private function responseNotFound() {
        // Indicate resource not found
        $this->response->statusCode(404);
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

    /**
     * Builds a Capsule data array to be sent over the wire
     *
     * @param array $capsule Capsule data
     * @return array
     */
    public static function buildCapsule($capsule) {
        $data = array();
        if (isset($capsule['id'])) {
            $data['id'] = $capsule['id'];
        }
        if (isset($capsule['name'])) {
            $data['name'] = $capsule['name'];
        }
        if (isset($capsule['lat'])) {
            $data['lat'] = $capsule['lat'];
        }
        if (isset($capsule['lng'])) {
            $data['lng'] = $capsule['lng'];
        }
        if (isset($capsule['etag'])) {
            $data['etag'] = $capsule['etag'];
        }
        return $data;
    }

    /**
     * Builds a Discovery data array to be sent over the wire
     *
     * @param array $capsule Capsule data
     * @param array $discovery Discovery data
     * @return array
     */
    public static function buildDiscovery($capsule, $discovery) {
        $data = ApiComponent::buildCapsule($capsule);
        if (isset($discovery['etag'])) {
            $data['etag'] = $discovery['etag'];
        }
        if (isset($discovery['rating'])) {
            $data['rating'] = $discovery['rating'];
        }
        if (isset($discovery['favorite'])) {
            $data['favorite'] = $discovery['favorite'];
        }
        return $data;
    }

}
