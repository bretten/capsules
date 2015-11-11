<?php
App::uses('AppController', 'Controller');

/**
 * Capsules Controller
 *
 * @property Capsule $Capsule
 * @property ApiComponent $Api
 * @property PaginatorComponent $Paginator
 */
class CapsulesController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Api', 'Paginator', 'RequestHandler', 'PaginatorBounding');

    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = array('Js');

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        // Get the Capsules
        $capsules = $this->Capsule->getForUser($this->Auth->user('id'), null, null, null, null, array(
            'includeDiscoveryStats' => true,
            'includeMemoirs' => true,
            'page' => 1,
            'limit' => ApiComponent::$objectLimit,
            'order' => \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC)
        ));
        $this->set('capsules', $capsules);
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        if (!$this->request->is('post') || !$this->request->is('ajax')) {
            throw new MethodNotAllowedException(__('Invalid request'));
        }

        if (!$this->Capsule->exists($this->request->data['id'])) {
            throw new NotFoundException(__('Invalid capsule'));
        }

        // Get the Capsule
        $capsule = $this->Capsule->find('first', array(
            'conditions' => array(
                'Capsule.id' => $this->request->data['id']
            ),
            'contain' => array(
                'Memoir' => array(
                    'fields' => array('Memoir.id', 'Memoir.title', 'Memoir.message')
                )
            )
        ));

        // Determine if the current User is the owner
        $isOwned = (isset($capsule['Capsule']['user_id']) && $capsule['Capsule']['user_id'] == $this->Auth->user('id')) ? true : false;
        // Determines if the Capsule is reachable
        $isReachable = false;
        // Determines if this is a new Discovery
        $isNewDiscovery = false;

        // Determine if it is a Discovery
        if (!$isOwned) {
            $discovery = $this->Capsule->Discovery->find('first', array(
                'conditions' => array(
                    'Discovery.capsule_id' => $this->request->data['id'],
                    'Discovery.user_id' => $this->Auth->user('id')
                )
            ));
            // If this is not a Discovery, see if it is range to be opened
            if (!$discovery) {
                if ($isReachable = $this->Capsule->isReachable(
                    $this->request->data['id'],
                    $this->request->data['lat'],
                    $this->request->data['lng'],
                    Configure::read('Map.UserLocation.DiscoveryRadius'))
                ) {
                    if ($discovery = $this->Capsule->Discovery->saveNew(
                        $this->request->data['id'],
                        $this->Auth->user('id'))
                    ) {
                        $isNewDiscovery = true;
                        $this->Session->setFlash(__('Congratulations!  You have discovered a new Capsule!'), 'notification', array('class' => 'alert-success', 'dismissible' => true));
                    } else {
                        $this->Session->setFlash(__('There was a problem opening the Capsule.  Please try again.'), 'notification', array('class' => 'alert-danger', 'dismissible' => true));
                    }
                } else {
                    $this->Session->setFlash(__('Sorry, you are not within range to open this Capsule.'), 'notification', array('class' => 'alert-danger', 'dismissible' => true));
                }
            }
        } else {
            // This Capsule is owned by the current User
            $discovery = null;
        }

        // Render the view
        $view = new View($this, false);
        $view->set(compact('isOwned', 'isReachable', 'capsule', 'discovery'));

        // Build the response body
        $body = array(
            'view' => $view->render('view')
        );
        // Append the Capsule id if this is a new Discovery
        if ($isNewDiscovery) {
            $body['newDiscovery'] = array(
                'id' => $capsule['Capsule']['id'],
                'lat' => $capsule['Capsule']['lat'],
                'lng' => $capsule['Capsule']['lng'],
                'name' => $capsule['Capsule']['name']
            );
        }
        // Send the response
        $this->response->body(json_encode($body));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {

    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     */
    public function edit($id = null) {
        $this->layout = null;

        if ($id && (!$this->Capsule->exists($id) || !$this->Capsule->ownedBy($this->Auth->user('id'), $id))) {
            throw new NotFoundException(__('Invalid capsule'));
        }

        if ($this->request->is(array('post', 'put'))) {
            // Do not render a view
            $this->autoRender = false;
            $this->layout = false;

            // Let the ApiComponent handle the request
            $this->Api->capsule($id);

            // Determine if this is a validation only request
            $isValidation = !isset($this->request->query['validate']) || $this->request->query['validate'] != 'false';

            // If the response was a success, display a success message
            if (!$isValidation && $this->response->statusCode() >= 200 && $this->response->statusCode() <= 299) {
                $this->Session->setFlash(__('The capsule has been saved.'), 'notification', array('class' => 'alert-success', 'dismissible' => true));
            }
            return;
        } else {
            if ($id) {
                $options = array(
                    'conditions' => array(
                        'Capsule.' . $this->Capsule->primaryKey => $id
                    ),
                    'contain' => array(
                        'Memoir'
                    )
                );
                $this->request->data = $this->Capsule->find('first', $options);
            }
        }
        // Pass the Capsule name
        $this->set('capsuleName', (($id) ? $this->Capsule->field('name', array($this->Capsule->primaryKey => $id)) : ''));
        // Use the add view
        $this->render('add');
    }

    /**
     * delete method
     *
     * @param string $id
     */
    public function delete($id = null) {
        // Do not render a view
        $this->autoRender = false;
        $this->layout = false;

        $this->Capsule->id = $id;
        if (!$this->Capsule->exists() || !$this->Capsule->ownedBy($this->Auth->user('id'))) {
            $this->response->statusCode(404);
        } else {
            $this->request->allowMethod('post', 'delete');
            if ($this->Capsule->delete()) {
                $this->response->statusCode(204);
            } else {
                $this->response->statusCode(500);
            }
        }
    }

    /**
     * map method
     *
     * Displays the map
     *
     * @return void
     */
    public function map() {

    }

    /**
     * Internal API method to return markers to the web version of the map
     *
     * @return void
     */
    public function points() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        $this->Api->points();
    }

    /**
     * Returns undiscovered markers to the web version of the map
     *
     * @return void
     */
    public function ping() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        $this->Api->ping();
    }

}
