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
