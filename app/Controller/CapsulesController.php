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
            if ($this->Capsule->save($this->request->data)) {
                $this->Session->setFlash(__('The capsule has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The capsule could not be saved. Please, try again.'));
            }
        }
        $users = $this->Capsule->User->find('list');
        $this->set(compact('users'));
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
            if ($this->Capsule->save($this->request->data)) {
                $this->Session->setFlash(__('The capsule has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The capsule could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Capsule.' . $this->Capsule->primaryKey => $id));
            $this->request->data = $this->Capsule->find('first', $options);
        }
        $users = $this->Capsule->User->find('list');
        $this->set(compact('users'));
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
}
