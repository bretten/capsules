<?php
App::uses('AppController', 'Controller');
/**
 * Discoveries Controller
 *
 * @property Discovery $Discovery
 * @property PaginatorComponent $Paginator
 */
class DiscoveriesController extends AppController {

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
        $this->Discovery->recursive = 0;
        $query = array(
            'conditions' => array(
                'Discovery.user_id' => $this->Auth->user('id')
            )
        );
        $this->Paginator->settings = $query;
        $this->set('discoveries', $this->Paginator->paginate());
    }

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function view($id = null) {
        if (!$this->Discovery->exists($id)) {
            throw new NotFoundException(__('Invalid discovery'));
        }
        $options = array('conditions' => array('Discovery.' . $this->Discovery->primaryKey => $id));
        $this->set('discovery', $this->Discovery->find('first', $options));
    }

/**
 * add method
 *
 * @return void
 */
    public function add() {
        if ($this->request->is('post')) {
            $this->Discovery->create();
            if ($this->Discovery->save($this->request->data)) {
                $this->Session->setFlash(__('The discovery has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The discovery could not be saved. Please, try again.'));
            }
        }
        $capsules = $this->Discovery->Capsule->find('list');
        $users = $this->Discovery->User->find('list');
        $this->set(compact('capsules', 'users'));
    }

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function edit($id = null) {
        if (!$this->Discovery->exists($id)) {
            throw new NotFoundException(__('Invalid discovery'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->Discovery->save($this->request->data)) {
                $this->Session->setFlash(__('The discovery has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The discovery could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Discovery.' . $this->Discovery->primaryKey => $id));
            $this->request->data = $this->Discovery->find('first', $options);
        }
        $capsules = $this->Discovery->Capsule->find('list');
        $users = $this->Discovery->User->find('list');
        $this->set(compact('capsules', 'users'));
    }

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function delete($id = null) {
        $this->Discovery->id = $id;
        if (!$this->Discovery->exists()) {
            throw new NotFoundException(__('Invalid discovery'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->Discovery->delete()) {
            $this->Session->setFlash(__('The discovery has been deleted.'));
        } else {
            $this->Session->setFlash(__('The discovery could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }
}
