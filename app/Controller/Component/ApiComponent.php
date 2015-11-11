<?php

App::uses('Component', 'Controller');

/**
 * Contains common API methods
 *
 * @property AuthComponent $Auth
 * @author Brett Namba
 */
class ApiComponent extends Component {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Auth');

    /**
     * Controller
     *
     * @var Controller
     */
    public $controller;

    /**
     * Capsule model
     *
     * @var Capsule
     */
    public $Capsule;

    /**
     * The number of entity resources to return when paging results
     *
     * @var int
     */
    public static $objectLimit = 5;

    /**
     * Called before the Controller's beforeFilter
     *
     * @param Controller $controller
     * @return void
     */
    public function initialize(Controller $controller) {
        $this->controller = $controller;
        if (isset($controller->Capsule) && $controller->Capsule instanceof Capsule) {
            $this->Capsule = $controller->Capsule;
        } else {
            $this->Capsule = ClassRegistry::init("Capsule");
        }
    }

    /**
     * Gets a Capsule by the specified ID.  Will determine if the Capsule is owned by the User.  If it is not
     * owned by the User, it will determine if the User has discovered it.
     *
     * @param mixed $id The ID of the Capsule
     */
    public function getCapsule($id) {
        // Make sure an ID was specified
        if (!$id) {
            throw new BadRequestException();
        }
        // Get the Capsule
        $capsule = $this->Capsule->getById($id);
        // If the Capsule could not be found, indicate resource not found
        if (!$capsule) {
            throw new NotFoundException();
        }

        // See if the User owns the Capsule
        $isOwned = $this->Auth->user('id') == $capsule['Capsule']['user_id'];

        // If the Capsule is not owned, see if it is discovered by the User
        $discovery = null;
        if (!$isOwned) {
            $discovery = $this->Capsule->Discovery->getByCapsuleIdForUser($id, $this->Auth->user('id'));
            // If the User does not own the Capsule or has not discovered it, indicate resource not found
            if (!$discovery) {
                throw new NotFoundException();
            }
        }

        $this->controller->set('capsule', $capsule);
        $this->controller->set('discovery', $discovery);
        $this->controller->set('isOwned', $isOwned);
    }

    /**
     * Gets all of the Capsules for the authenticated User.  The Capsules can be filtered by page or by bounding
     * rectangle if query parameters are added to the HTTP request.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function getUserCapsules(CakeRequest $request) {
        // Query
        $query = array(
            'includeDiscoveryStats' => true,
            'includeMemoirs' => true
        );
        // Parse pagination query parameters
        $query = $this->parsePagination($request->query, $query);
        // Parse the filter query parameter
        $query = $this->parseFilter($request->query, $query);
        // Parse the search terms query parameter
        $query = $this->parseSearch($request->query, $query);
        // Parse the bounding rectangle data
        $data = $this->parseBoundingRectangle($request->query);

        // Get the Capsules
        $capsules = $this->Capsule->getForUser($this->Auth->user('id'), $data['latNE'], $data['lngNE'], $data['latSW'],
            $data['lngSW'], $query);

        $this->controller->set('capsules', $capsules);
    }

    /**
     * Gets all of the Capsule discoveries for the authenticated User.  They can be filtered by page or by
     * bounding rectangle if query parameters are added to the HTTP request.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function getUserDiscoveries(CakeRequest $request) {
        // Query
        $query = array(
            'includeDiscoveryStats' => true,
            'includeMemoirs' => true
        );
        // Parse pagination query parameters
        $query = $this->parsePagination($request->query, $query);
        // Parse the filter query parameter
        $query = $this->parseFilter($request->query, $query);
        // Parse the search terms query parameter
        $query = $this->parseSearch($request->query, $query);
        // Parse the bounding rectangle data
        $data = $this->parseBoundingRectangle($request->query);

        // Get the Capsule discoveries
        $capsules = $this->Capsule->getDiscoveredForUser($this->Auth->user('id'), $data['latNE'], $data['lngNE'],
            $data['latSW'], $data['lngSW'], $query);

        $this->controller->set('capsules', $capsules);
    }

    /**
     * Serves the file associated with the Memoir as the response
     *
     * @param mixed $id ID of the Memoir
     */
    public function getMemoirFile($id) {
        // Make sure an ID was specified
        if (!$id) {
            throw new BadRequestException();
        }
        // Get the Memoir
        $memoir = $this->Capsule->Memoir->getById($id);
        // If no Memoir exists or if there is no Capsule ID, indicate no resource found
        if (!$memoir || !isset($memoir['Memoir']) || !isset($memoir['Memoir']['capsule_id'])) {
            throw new NotFoundException();
        }
        // Also indicate no resource found if the User does not own the Capsule and have not discovered it
        if (!$this->Capsule->ownedBy($this->Auth->user('id'), $memoir['Memoir']['capsule_id'])) {
            if (!$this->Capsule->Discovery->isDiscoveredByUser($memoir['Memoir']['capsule_id'],
                $this->Auth->user('id'))
            ) {
                throw new NotFoundException();
            }
        }

        // Add headers to indicate an image is being served
        header("Content-Type: " . $memoir['Memoir']['file_type']);
        header("Content-Length: " . $memoir['Memoir']['file_size']);
        header("Last-Modified: " . date(DATE_RFC2822, strtotime($memoir['Memoir']['modified'])));
        // Set the image as the response body
        readfile($memoir['Memoir']['file_location'] . DS . $memoir['Memoir']['file_public_name']);
        return;
    }

    /**
     * Discovers all Capsules for the authenticated User in a bounding circle around the latitude and longitude
     * specified in the HTTP request.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function discoverAllInRadius(CakeRequest $request) {
        // Parse the latitude and longitude
        $data = $this->parseBoundingCircle($request->data);
        // Make sure the latitude and longitude values exist
        if ($data['lat'] == null || $data['lng'] == null) {
            throw new BadRequestException();
        }
        // Query
        $query = array(
            'includeDiscoveryStats' => true
        );
        // Get all Capsules within the radius
        $capsules = $this->Capsule->getUndiscoveredForUser($this->Auth->user('id'), $data['lat'], $data['lng'],
            Configure::read('Map.UserLocation.SearchRadius'), $query);

        // See if there are any Capsules to discover
        if (empty($capsules)) {
            // Indicate a no content response
            $this->sendNoContentResponse();
            return;
        } else {
            // Discover all the Capsules within the radius for the specified User
            if ($this->Capsule->Discovery->createMany($this->Auth->user('id'),
                Hash::extract($capsules, '{n}.Capsule.id'))
            ) {
                $this->controller->set('capsules', $capsules);
                return;
            } else {
                // See if there were validation errors or if an internal error occurred
                $this->handleSaveError($this->Capsule->Discovery);
                return;
            }
        }
    }

    /**
     * Handles a POST request for a single Capsule resource.  If a validation query parameter was specified
     * in the HTTP request then the Capsule will only be validated.  Otherwise, it will be created.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function postCapsule(CakeRequest $request) {
        // Check for the validation flag in the request query parameters
        $isValidationRequest = $this->parseValidationFlag($request->query);

        // If the validation was flag, just validate, otherwise attempt to create the Capsule
        if ($isValidationRequest) {
            $this->validateCapsule($request);
        } else {
            $this->createCapsule($request);
        }
    }

    /**
     * Validates the Capsule and associated Memoir data.  On success, an HTTP "no content" code will be sent.
     * Otherwise, the validation error messages will be sent as a JSON response.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function validateCapsule(CakeRequest $request) {
        // Parse the Capsule data
        $data = $this->parseCapsuleData($request->data);
        // Parse the Memoir data
        $data = $this->parseMemoirData($request->data, $data);

        // Validate
        if ($this->Capsule->saveAll($data, array('deep' => true, 'fieldList' => $this->Capsule->fieldListValidate,
            'validate' => 'only'))
        ) {
            // Indicate a no content response
            $this->sendNoContentResponse();
            return;
        } else {
            // See if there were validation errors or if an internal error occurred
            $this->handleSaveError($this->Capsule);
            return;
        }
    }

    /**
     * Attempts to save the Capsule and Memoir data in the HTTP request.  If successful, the capsule data will
     * be returned.
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function createCapsule(CakeRequest $request) {
        // Parse the Capsule data
        $data = $this->parseCapsuleData($request->data);
        // Parse the Memoir data
        $data = $this->parseMemoirData($request->data, $data);

        // Save
        if ($this->Capsule->saveAllWithUploads($data,
            array('deep' => true, 'fieldList' => $this->Capsule->fieldListCreate, 'associateOwner' => true,
                'updateCtagForUser' => $this->Auth->user('id')))
        ) {
            // Get the ID of the new Capsule
            $capsuleId = $this->Capsule->getLastInsertID();
            // Get the Capsule
            $capsule = $this->Capsule->getByIdForUser($capsuleId, $this->Auth->user('id'));

            $this->controller->set('capsule', $capsule);
            return;
        } else {
            // See if there were validation errors or if an internal error occurred
            $this->handleSaveError($this->Capsule);
            return;
        }
    }

    /**
     * Updates the Discovery specified by the ID
     *
     * @param mixed $id The ID of the Discovery to update
     * @param CakeRequest $request The current HTTP request
     * @throws Exception
     */
    public function updateDiscovery($id, CakeRequest $request) {
        // Make sure an ID was specified
        if (!$id) {
            throw new BadRequestException();
        }
        // Set the ID
        $this->Capsule->Discovery->id = $id;
        // Make sure the Discovery exists and that it is owned by the User
        if (!$this->Capsule->Discovery->exists() || !$this->Capsule->Discovery->ownedBy($this->Auth->user('id'))) {
            // Throw a not found even if it exists but is not owned by the User so that users cannot guess IDs
            throw new NotFoundException();
        }

        // Parse the request
        $data = $this->parseDiscoveryData($request->data);

        // Save
        if ($this->Capsule->Discovery->save($data, array('fieldList' => $this->Capsule->Discovery->fieldListUpdate))) {
            // Indicate a no content response
            $this->sendNoContentResponse();
            return;
        } else {
            // See if there were validation errors or if an internal error occurred
            $this->handleSaveError($this->Capsule->Discovery);
            return;
        }
    }

    /**
     * Creates a User with the data from the HTTP request and returns the authentication token in the response
     *
     * @param CakeRequest $request The current HTTP request
     */
    public function createUser(CakeRequest $request) {
        // Parse the User data
        $data = $this->parseUserData($request->data);

        // Save
        if ($this->Capsule->User->save($data, array(
            'confirmPassword' => true, 'fieldList' => $this->Capsule->User->fieldListCreate,
            'assignNewAuthToken' => true))
        ) {
            // Get the newly created User's authentication token
            $token = $this->Capsule->User->getAuthTokenForUser($this->Capsule->User->getLastInsertID());

            $this->controller->set('token', $token);
            return;
        } else {
            // See if there were validation errors or if an internal error occurred
            $this->handleSaveError($this->Capsule->User);
            return;
        }
    }

    /**
     * Attempts to delete the Capsule specified by the ID
     *
     * @param mixed $id The ID of the Capsule to delete
     */
    public function deleteCapsule($id) {
        // Make sure the ID exists
        if (!$id) {
            throw new BadRequestException();
        }
        // Set the ID
        $this->Capsule->id = $id;
        // Make sure the Capsule exists and that it belongs to the authenticated User
        if (!$this->Capsule->exists() || !$this->Capsule->ownedBy($this->Auth->user('id'))) {
            throw new NotFoundException();
        }

        // Attempt to delete the Capsule
        if ($this->Capsule->delete()) {
            // Indicate a no content response
            $this->sendNoContentResponse();
            return;
        } else {
            throw new InternalErrorException();
        }
    }

    /**
     * Authenticates a User and returns a new authentication token in the HTTP response
     */
    public function authenticate() {
        // Make sure the User authenticates
        if (!$this->Auth->login()) {
            throw new UnauthorizedException();
        }

        // Save a new authentication token for the User
        if ($this->Capsule->User->setNewAuthToken($this->Auth->user('id'))) {
            // Get the newly created User's authentication token
            $this->controller->set('token', $this->Capsule->User->getAuthTokenForUser($this->Auth->user('id')));
            return;
        } else {
            throw new InternalErrorException();
        }
    }

    /**
     * Parses the validation flag out of the HTTP request parameters and determines if it was set to true
     *
     * @param array $requestParams The HTTP request parameters
     * @return bool True if the validation flag was sent and set to true, otherwise false
     */
    private function parseValidationFlag(array $requestParams) {
        return isset($requestParams['validate']) && $requestParams['validate'] == 'true';
    }

    /**
     * Parses the Capsule data from the HTTP request parameters
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data to append the Capsule data to
     * @return array The data array with the newly appended data
     */
    private function parseCapsuleData(array $requestParams, array $data = array()) {
        if (!isset($data['Capsule'])) {
            $data['Capsule'] = array();
        }
        if (isset($requestParams['Capsule']['name'])) {
            $data['Capsule']['name'] = $requestParams['Capsule']['name'];
        }
        if (isset($requestParams['Capsule']['lat'])) {
            $data['Capsule']['lat'] = $requestParams['Capsule']['lat'];
        }
        if (isset($requestParams['Capsule']['lng'])) {
            $data['Capsule']['lng'] = $requestParams['Capsule']['lng'];
        }

        return $data;
    }

    /**
     * Parses the Memoir data from the HTTP request parameters
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data to append the Memoir data to
     * @return array The data array with the newly appended data
     */
    private function parseMemoirData(array $requestParams, array $data = array()) {
        if (!isset($data['Memoir'])) {
            $data['Memoir'] = array();
        }
        if (isset($requestParams['Memoir']) && is_array($requestParams['Memoir'])) {
            foreach ($requestParams['Memoir'] as $key => $memoir) {
                if (!is_numeric($key)) {
                    continue;
                }
                if (!isset($data['Memoir'][$key])) {
                    $data['Memoir'][$key] = array();
                }
                if (isset($memoir['title'])) {
                    $data['Memoir'][$key]['title'] = $memoir['title'];
                }
                if (isset($memoir['message'])) {
                    $data['Memoir'][$key]['message'] = $memoir['message'];
                }
                if (isset($memoir['file'])) {
                    $data['Memoir'][$key]['file'] = $memoir['file'];
                }
            }
        }

        return $data;
    }

    /**
     * Parses the Discovery data from the HTTP request parameters
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data to append the Discovery data to
     * @return array The data array with the newly appended data
     */
    private function parseDiscoveryData(array $requestParams, array $data = array()) {
        if (isset($requestParams['favorite']) && is_numeric($requestParams['favorite'])) {
            $data['favorite'] = $requestParams['favorite'] != 1 ? 0 : 1;
        }
        if (isset($requestParams['rating']) && is_numeric($requestParams['rating'])) {
            if ($requestParams['rating'] >= 1) {
                $data['rating'] = 1;
            } else if ($requestParams['rating'] <= -1) {
                $data['rating'] = -1;
            } else {
                $data['rating'] = 0;
            }
        }

        return $data;
    }

    /**
     * Parses the User data from the HTTP request parameters
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data to append the User data to
     * @return array The data array with the newly appended data
     */
    private function parseUserData(array $requestParams, array $data = array()) {
        if (isset($requestParams['username'])) {
            $data['username'] = $requestParams['username'];
        }
        if (isset($requestParams['email'])) {
            $data['email'] = $requestParams['email'];
        }
        if (isset($requestParams['password'])) {
            $data['password'] = $requestParams['password'];
        }
        if (isset($requestParams['confirm_password'])) {
            $data['confirm_password'] = $requestParams['confirm_password'];
        }

        return $data;
    }

    /**
     * Looks for any pagination related HTTP query parameters.  If they exist it will parse them and append
     * them to the database query array
     *
     * @param array $requestParams The HTTP request query parameters
     * @param array $query The database query array
     * @return array The database query with the pagination parameters appended
     */
    private function parsePagination(array $requestParams, array $query = array()) {
        // Parse the page
        if (isset($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_PAGE])
            && is_numeric($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_PAGE])
        ) {
            $query['page'] = $requestParams[\Capsules\Http\RequestContract::PARAM_NAME_PAGE];
            // Add the object limit
            $query['limit'] = ApiComponent::$objectLimit;
        }
        // Parse the sort order
        if (isset($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SORT])
            && is_numeric($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SORT])
        ) {
            $query['order'] = \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                $requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SORT]);
        } else {
            $query['order'] = \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC);
        }

        return $query;
    }

    /**
     * Parses the filter HTTP query parameter from the HTTP request and appends it to the database
     * query array
     *
     * @param array $requestParams The HTTP request query parameters
     * @param array $query The database query array
     * @return array The updated database query array
     */
    private function parseFilter(array $requestParams, array $query = array()) {
        // Parse the filter
        if (isset($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_FILTER])
            && is_numeric($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_FILTER])
        ) {
            $query = \Capsules\Http\RequestContract::appendCapsuleFilterToQuery(
                $requestParams[\Capsules\Http\RequestContract::PARAM_NAME_FILTER], $query);
        }

        return $query;
    }

    /**
     * Parses the search term HTTP query parameter from the HTTP request and appends it to the database query
     * array
     *
     * @param array $requestParams The HTTP request query parameters
     * @param array $query The database query array
     * @return array The updated database query array
     */
    private function parseSearch(array $requestParams, array $query = array()) {
        // Parse search keywords
        if (isset($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SEARCH])
            && $requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SEARCH]
        ) {
            $query['searchString'] = trim(urldecode($requestParams[\Capsules\Http\RequestContract::PARAM_NAME_SEARCH]));
        }

        return $query;
    }

    /**
     * Looks for any bounding circle related parameters in the HTTP request parameters.  If they are found, they
     * will be appended to the data array.
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data array to append to
     * @return array The data array with the appended parameters
     */
    private function parseBoundingCircle(array $requestParams, array $data = array()) {
        // Parse the latitude
        if (isset($requestParams['lat']) && is_numeric($requestParams['lat'])) {
            $data['lat'] = $requestParams['lat'];
        } else {
            $data['lat'] = null;
        }
        // Parse the longitude
        if (isset($requestParams['lng']) && is_numeric($requestParams['lng'])) {
            $data['lng'] = $requestParams['lng'];
        } else {
            $data['lng'] = null;
        }

        return $data;
    }

    /**
     * Looks for any bounding rectangle related parameters in the HTTP request parameters.  If they are found, they
     * will be appended to the data array.
     *
     * @param array $requestParams The HTTP request parameters
     * @param array $data The data array to append to
     * @return array The data array with the appended parameters
     */
    private function parseBoundingRectangle(array $requestParams, array $data = array()) {
        $spatialValues = array('latNE', 'lngNE', 'latSW', 'lngSW');
        foreach ($spatialValues as $spatialValue) {
            if (isset($requestParams[$spatialValue]) && is_numeric($requestParams[$spatialValue])) {
                $data[$spatialValue] = $requestParams[$spatialValue];
            } else {
                $data[$spatialValue] = null;
            }
        }

        return $data;
    }

    /**
     * Should be used on a Model after a save attempt is made.  If any validation errors were found, it will create
     * a HTTP response containing the validation messages.  Otherwise, since cause of the failed save was not
     * due to validation, throw an exception indicating a server error.
     *
     * @param Model $model The Model to check for validation errors
     */
    private function handleSaveError(Model $model) {
        // Check for validation errors
        if (isset($model->validationErrors) && !empty($model->validationErrors)) {
            // Send a HTTP response containing the validation errors
            $this->sendValidationErrorResponse($model->validationErrors);
        } else {
            throw new InternalErrorException();
        }
    }

    /**
     * Overrides the View, sets the HTTP response code to indicate no content, and sets an empty response body
     */
    private function sendNoContentResponse() {
        // Indicate that there is no content
        $this->setStatusCodeNoContent();
        // Override the view with empty content
        $this->overrideView(null);
    }

    /**
     * Overrides the View, sets the HTTP response code to indicate a bad request, and sets the response body
     * to contain the specified validation error messages
     *
     * @param array $validationMessages An array of validation error messages
     */
    private function sendValidationErrorResponse(array $validationMessages = array()) {
        // Indicate a bad request
        $this->setStatusCodeBadRequest();
        // Build the response body
        $responseBody = array(
            'name' => 'Bad Request',
            'message' => 'Bad Request',
            'url' => $this->controller->request->here(),
            'messages' => $validationMessages
        );
        // Override the view with the response body
        $this->overrideView($responseBody, /* jsonEncodeResponse */
            true);
    }

    /**
     * Overrides the View by telling the Controller not to auto render the View.  The response body parameter is used
     * as the actual HTTP response body and will be JSON encoded if indicated to do so.
     *
     * @param string $responseBody The HTTP response body
     * @param bool|true $jsonEncodeResponse Whether or not to encode the HTTP response in JSON
     */
    private function overrideView($responseBody, $jsonEncodeResponse = true) {
        $this->controller->autoRender = false;
        $this->controller->layout = false;

        if ($jsonEncodeResponse && is_array($responseBody)) {
            $this->controller->response->type('json');
            $responseBody = json_encode($responseBody);
        }

        $this->controller->response->body($responseBody);
    }

    /**
     * Sets the HTTP response status code to indicate "No content".
     */
    private function setStatusCodeNoContent() {
        $this->controller->response->statusCode(204);
    }

    /**
     * Sets the HTTP status code to indicate "Bad request".
     */
    private function setStatusCodeBadRequest() {
        $this->controller->response->statusCode(400);
    }

}
