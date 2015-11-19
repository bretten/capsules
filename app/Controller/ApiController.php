<?php
App::uses('AppController', 'Controller');

/**
 * API Controller
 *
 * @property ApiComponent $Api
 */
class ApiController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Api', 'RequestHandler');

    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = array('JsonResponse');

    /**
     * uses
     *
     * @var array
     */
    public $uses = array('Capsule', 'Discovery', 'User');

    /**
     * The current HTTP method for this request
     *
     * @var string
     */
    private $currentHttpMethod;

    /**
     * The message to display when aN HTTP method has not been implemented
     *
     * @var string
     */
    private static $NOT_IMPLEMENTED_MESSAGE = "Not implemented";

    /**
     * beforeFilter method
     *
     * @return void
     */
    public function beforeFilter() {
        parent::beforeFilter();
        // Get the HTTP method type
        $this->currentHttpMethod = strtoupper(trim($this->request->method()));
        // Determine the authentication type
        if ($this->request->params['action'] == "token" && $this->currentHttpMethod == "GET") {
            // Only use non-token authentication when authenticating Users since they may not have a valid token
            $this->Auth->authenticate = array('Basic');
        } else {
            // Force authentication by token for all other API methods
            $this->Auth->authenticate = array('Token');
        }
        // Set the view path
        $this->setViewPath($this->request->params['action'], strtolower($this->currentHttpMethod));
    }

    /**
     * API method to handle different HTTP methods on a collection of Capsule resources
     */
    public function capsules() {
        switch ($this->currentHttpMethod) {
            case "GET":
                $this->checkAuthentication();
                $this->Api->getUserCapsules($this->request);
                break;
            case "POST":
            case "PUT":
            case "DELETE":
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a collection of Discovery Capsule resources
     */
    public function discoveries() {
        switch ($this->currentHttpMethod) {
            case "GET":
                $this->checkAuthentication();
                $this->Api->getUserDiscoveries($this->request);
                break;
            case "POST":
                $this->checkAuthentication();
                $this->Api->discoverAllInRadius($this->request);
                break;
            case "PUT":
            case "DELETE":
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a Capsule resource
     *
     * @param mixed $id The ID of the Capsule
     */
    public function capsule($id = null) {
        switch ($this->currentHttpMethod) {
            case "GET":
                $this->checkAuthentication();
                $this->Api->getCapsule($id);
                break;
            case "POST":
                $this->checkAuthentication();
                if ($id == null) {
                    $this->Api->postCapsule($this->request);
                    break;
                }
            case "PUT":
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
            case "DELETE":
                $this->checkAuthentication();
                $this->Api->deleteCapsule($id);
                break;
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a Discovery resource
     *
     * @param mixed $id The ID of the Discovery
     */
    public function discovery($id = null) {
        switch ($this->currentHttpMethod) {
            case "GET":
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
            case "POST":
                $this->checkAuthentication();
                $this->Api->updateDiscovery($id, $this->request);
                break;
            case "PUT":
            case "DELETE":
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a Memoir resource
     *
     * @param mixed $id The ID of the Memoir
     */
    public function memoir($id = null) {
        switch ($this->currentHttpMethod) {
            case "GET":
                $this->checkAuthentication();
                $this->Api->getMemoirFile($id);
                break;
            case "POST":
            case "PUT":
            case "DELETE":
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a User resource
     *
     * @param mixed $id The ID of the User
     */
    public function user($id = null) {
        switch ($this->currentHttpMethod) {
            case "GET":
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
            case "POST":
                if ($id == null) {
                    $this->Auth->allow('user');
                    $this->Api->createUser($this->request);
                    break;
                }
            case "PUT":
            case "DELETE":
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on an authentication token
     */
    public function token() {
        switch ($this->currentHttpMethod) {
            case "GET":
                $this->Api->authenticate();
                break;
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * API method to handle different HTTP methods on a collection tag
     *
     * @param string $type The type of collection tag
     */
    public function ctag($type = "") {
        switch ($this->currentHttpMethod) {
            case "GET":
                if ($this->response->type() == 'application/json') {
                    if ($type == "capsules") {
                        $this->Api->getCtagCapsules();
                        break;
                    } else if ($type == "discoveries") {
                        $this->Api->getCtagDiscoveries();
                        break;
                    }
                }
            default:
                throw new NotImplementedException(__(ApiController::$NOT_IMPLEMENTED_MESSAGE));
        }
    }

    /**
     * Checks if the User has been authenticated.  If the user is not authenticated, send a response
     * indicating they need to be authenticated.
     */
    private function checkAuthentication() {
        if (!$this->Auth->user()) {
            throw new UnauthorizedException();
        }
    }

    /**
     * Based on the ApiController action and the HTTP method of the current request, will determine which
     * view file to render since it is possible for a single action to render different view files depending
     * on the HTTP method.
     *
     * @param string $action The controller action
     * @param string $httpMethod The HTTP method of the current request
     */
    private function setViewPath($action, $httpMethod) {
        $this->viewPath = $this->name . DS . $action . DS . $httpMethod;
    }

}
