<?php
App::uses('AppController', 'Controller');

/**
 * API Controller
 *
 * @property PaginatorComponent $Paginator
 */
class ApiController extends AppController {

/**
 * Components
 *
 * @var array
 */
    public $components = array('Api', 'Paginator');

/**
 * uses
 *
 * @var array
 */
    public $uses = array('Capsule', 'Discovery', 'User');

/**
 * beforeFilter method
 *
 * @return void
 */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->autoRender = false;
        $this->layout = false;
        if ($this->request->params['action'] === 'authenticate') {
            $this->Auth->authenticate = array('Basic');
        } else {
            $this->Auth->authenticate = array('Token');
        }
        // Don't require authentication when registering or authenticating
        $this->Auth->allow(array('register', 'authenticate'));
    }

/**
 * API method used to handle User registrations
 *
 * @return void
 */
    public function register() {
        $this->Api->register();
    }

/**
 * API method to handle authenticating Users.  Response body contains an authentication token
 * to be used in future API calls.
 *
 * @return void
 */
    public function authenticate() {
        $this->Api->authenticate();
    }

/**
 * API method to return a User's Capsules and Discoveries
 *
 * @return void
 */
    public function points() {
        $this->Api->points();
    }

/**
 * API method to retrieve a collection's ctag
 *
 * @param string $collection
 * @return void
 */
    public function ctag($collection = null) {
        $this->Api->ctag($collection);
    }

/**
 * API method to retrieve etags for a collection
 *
 * @param string $collection
 * @return void
 */
    public function status($collection = null) {
        $this->Api->status($collection);
    }

/**
 * API method to retrieve data for specified resources
 *
 * @param string $collection
 * @return void
 */
    public function report($collection = null) {
        $this->Api->report($collection);
    }

/**
 * API method to retrieve undiscovered Capsules in the User's radius.
 *
 * @return void
 */
    public function ping() {
        $this->Api->ping();
    }

/**
 * API function to "open"/"discover" a Capsule.
 *
 * @return void
 */
    public function open() {
        $this->Api->open();
    }

/**
 * API method to handle POST, GET, DELETE on a single Capsule
 *
 * @return void
 */
    public function capsule($id = null) {
        $this->Api->capsule($id);
    }

/**
 * API method to handle POST, GET, DELETE on a single Discovery
 *
 * @return void
 */
    public function discovery($id = null) {
        $this->Api->discovery($id);
    }

}