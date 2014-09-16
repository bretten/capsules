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
    public $components = array('Paginator', 'RequestHandler');

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
        $query = array(
            'conditions' => array(
                'Capsule.user_id' => $this->Auth->user('id')
            )
        );
        $this->Paginator->settings = $query;
        $this->set('capsules', $this->Paginator->paginate());
    }

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function view() {
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
            )
        ));

        // Determine if the current User is the owner
        $isOwned = (isset($capsule['Capsule']['user_id']) && $capsule['Capsule']['user_id'] == $this->Auth->user('id')) ? true : false;
        // Determines if the Capsule is reachable
        $isReachable = false;

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
                    Configure::read('Capsule.Search.Radius')
                )) {
                    if ($discovery = $this->Capsule->Discovery->saveNew(
                        $this->request->data['id'],
                        $this->Auth->user('id')
                    )) {
                        $this->Session->setFlash(__('Congratulations!  You have discovered a new Capsule!'));
                    } else {
                        $this->Session->setFlash(__('There was a problem opening the Capsule.  Please try again.'));
                    }
                } else {
                    $this->Session->setFlash(__('Sorry, you are not within range to open this Capsule.'));
                }
            }
        } else {
            // This Capsule is owned by the current User
            $discovery = null;
        }
        
        $this->set(compact('isOwned', 'isReachable', 'capsule', 'discovery'));
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
                'deep' => true, 'associateOwner' => true, 'updateCtagForUser' => $this->Auth->user('id')
            ))) {
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
 * @return void
 */
    public function edit($id = null) {
        if (!$this->Capsule->exists($id)) {
            throw new NotFoundException(__('Invalid capsule'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->Capsule->saveDiff($this->request->data, array(
                'deep' => true, 'removeHasMany' => 'Memoir', 'associateOwner' => true, 'updateCtagForUser' => $this->Auth->user('id')
            ))) {
                $this->Session->setFlash(__('The capsule has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The capsule could not be saved. Please, try again.'));
            }
        } else {
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
        // Use the add view
        $this->render('add');
    }

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function delete($id = null) {
        $this->Capsule->id = $id;
        if (!$this->Capsule->exists()) {
            throw new NotFoundException(__('Invalid capsule'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->Capsule->delete()) {
            $this->Session->setFlash(__('The capsule has been deleted.'));
        } else {
            $this->Session->setFlash(__('The capsule could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
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

        $body = array();

        if ($this->request->is('post')) {
            if (!isset($this->request->data['latNE']) || !is_numeric($this->request->data['latNE'])
                || !isset($this->request->data['lngNE']) || !is_numeric($this->request->data['lngNE'])
                || !isset($this->request->data['latSW']) || !is_numeric($this->request->data['latSW'])
                || !isset($this->request->data['lngSW']) || !is_numeric($this->request->data['lngSW'])
            ) {
                $this->response->statusCode(400);
            } else {
                $capsules = $this->Capsule->getInRectangle(
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
                $body['capsules'] = Hash::map($capsules, "{n}.Capsule", function($data) {
                    return array(
                        'data' => array(
                            'id' => $data['id'],
                            'name' => $data['name'],
                            'lat' => $data['lat'],
                            'lng' => $data['lng']
                        )
                    );
                });

                $discoveries = $this->Capsule->getDiscovered(
                    $this->Auth->user('id'),
                    $this->request->data['latNE'],
                    $this->request->data['lngNE'],
                    $this->request->data['latSW'],
                    $this->request->data['lngSW']
                );
                $body['discoveries'] = Hash::map($discoveries, "{n}.Capsule", function($data) {
                    return array(
                        'data' => array(
                            'id' => $data['id'],
                            'name' => $data['name'],
                            'lat' => $data['lat'],
                            'lng' => $data['lng']
                        )
                    );
                });
            }
        } else {
            $this->response->statusCode(405);
        }

        // Indicate success if the response body was built
        if ($body) {
            $this->response->statusCode(200);
        }

        $this->response->body(json_encode($body));
    }

}
