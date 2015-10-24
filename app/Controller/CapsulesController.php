<?php
App::uses('AppController', 'Controller');

/**
 * Capsules Controller
 *
 * @property Capsule $Capsule
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
        if (!$this->request->is('ajax')) {
            throw new MethodNotAllowedException(__('Invalid request'));
        }

        $this->layout = 'ajax';

        $this->Capsule->recursive = 0;
        // Add the virtual fields for favorite count and total rating
        $this->Capsule->virtualFields['discovery_count'] = Capsule::FIELD_DISCOVERY_COUNT;
        $this->Capsule->virtualFields['favorite_count'] = Capsule::FIELD_FAVORITE_COUNT;
        $this->Capsule->virtualFields['total_rating'] = Capsule::FIELD_RATING;
        // Build the query options
        $query = array(
            'conditions' => array(
                'Capsule.user_id' => $this->Auth->user('id')
            ),
            'joins' => array(
                array(
                    'table' => 'discoveries',
                    'alias' => 'DiscoveryStat',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = DiscoveryStat.capsule_id',
                    )
                )
            ),
            'group' => array('Capsule.id')
        );
        // Search refinement
        $search = (isset($this->request->query['search']) && $this->request->query['search']) ? $this->request->query['search'] : "";
        if ($search) {
            $query['conditions']['Capsule.name LIKE'] = "%" . urldecode($search) . "%";
        }
        // Make sure that the current page does not exceed the actual number of pages
        $this->PaginatorBounding->setLimit(Configure::read('Pagination.Result.Count'));
        $this->PaginatorBounding->checkBounds($this->Capsule, $query);
        // Set the pagination limit
        $query['limit'] = Configure::read('Pagination.Result.Count');
        $this->Paginator->settings = $query;
        $this->set('capsules', $this->Paginator->paginate());
        $this->set(compact('search'));
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
        if ($this->request->is('post')) {
            $this->Capsule->create();
            if ($this->Capsule->saveAll($this->request->data, array(
                'deep' => true, 'associateOwner' => true, 'updateCtagForUser' => $this->Auth->user('id')))
            ) {
                $this->Session->setFlash(__('The capsule has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The capsule could not be saved. Please, try again.'));
            }
        }
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
