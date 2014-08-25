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
    public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
    public function index() {
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
    public function view($id = null) {
        if (!$this->Capsule->exists($id)) {
            throw new NotFoundException(__('Invalid capsule'));
        }
        $options = array('conditions' => array('Capsule.' . $this->Capsule->primaryKey => $id));
        $this->set('capsule', $this->Capsule->find('first', $options));
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
 * API method that returns undiscovered Capsules within the User's radius.
 *
 * @return void
 */
    public function ping() {
        $this->autoRender = false;
        $this->layout = false;

        $results = $this->Capsule->getUndiscovered(
            $this->StatelessAuth->user('id'),
            $this->request->data['lat'],
            $this->request->data['lng'],
            Configure::read('Capsule.Search.Radius')
        );

        $this->response->body(json_encode($results));
    }

/**
 * API method that handles opening a Capsule for a User.
 *
 * @return void
 */
    public function open() {
        $this->autoRender = false;
        $this->layout = false;

        $body = array(
            'success' => false
        );

        if ($this->Capsule->isReachable(
            $this->request->data['capsule'],
            $this->request->data['lat'],
            $this->request->data['lng'],
            Configure::read('Capsule.Search.Radius')
        )) {
            if (!$this->Capsule->Discovery->created(
                $this->request->data['capsule'],
                $this->StatelessAuth->user('id')
            )) {
                if ($insert = $this->Capsule->Discovery->saveNew(
                    $this->request->data['capsule'],
                    $this->StatelessAuth->user('id')
                )) {
                    $body['success'] = true;
                    $body['data'] = $insert;
                }
            }
        }

        $this->response->body(json_encode($body));
    }

/**
 * API method that handles marking a Discovery/Capsule as a favorite for a User.
 */
    public function favorite() {
        $this->autoRender = false;
        $this->layout = false;

        $body = array(
            'success' => false
        );

        if ($discovery = $this->Capsule->Discovery->created(
            $this->request->data['capsule'],
            $this->StatelessAuth->user('id')
        )) {
            // Build the save data
            $data = array(
                'Discovery' => array(
                    'id' => $discovery['Discovery']['id'],
                    'favorite' => (boolean)$this->request->data['favorite']
                )
            );
            if ($result = $this->Capsule->Discovery->save($data)) {
                $body['success'] = true;
                $body['favorite'] = (boolean)$this->request->data['favorite'];
            }
        }

        $this->response->body(json_encode($body));
    }

/**
 * API method to handle rating a Discovery.
 */
    public function rate() {
        $this->autoRender = false;
        $this->layout = false;

        $body = array(
            'success' => false
        );

        if ($discovery = $this->Capsule->Discovery->created(
            $this->request->data['capsule'],
            $this->StatelessAuth->user('id')
        )) {
            // Build the save data
            $data = array(
                'Discovery' => array(
                    'id' => $discovery['Discovery']['id'],
                    'rating' => $this->request->data['rating']
                )
            );
            if ($result = $this->Capsule->Discovery->save($data)) {
                $body['success'] = true;
            }
        }

        $this->response->body(json_encode($body));
    }
}
