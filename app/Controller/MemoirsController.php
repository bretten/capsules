<?php
App::uses('AppController', 'Controller');
/**
 * Memoirs Controller
 *
 * @property Memoir $Memoir
 * @property PaginatorComponent $Paginator
 */
class MemoirsController extends AppController {

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
        $this->Memoir->recursive = 0;
        $this->set('memoirs', $this->Paginator->paginate());
    }

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function view($id = null) {
        if (!$this->Memoir->exists($id)) {
            throw new NotFoundException(__('Invalid memoir'));
        }
        $options = array('conditions' => array('Memoir.' . $this->Memoir->primaryKey => $id));
        $this->set('memoir', $this->Memoir->find('first', $options));
    }

/**
 * add method
 *
 * @return void
 */
    public function add() {
        if ($this->request->is('post')) {
            $this->Memoir->create();
            if ($this->Memoir->save($this->request->data)) {
                $this->Session->setFlash(__('The memoir has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The memoir could not be saved. Please, try again.'));
            }
        }
        $capsules = $this->Memoir->Capsule->find('list');
        $this->set(compact('capsules'));
    }

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function edit($id = null) {
        if (!$this->Memoir->exists($id)) {
            throw new NotFoundException(__('Invalid memoir'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->Memoir->save($this->request->data)) {
                $this->Session->setFlash(__('The memoir has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The memoir could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Memoir.' . $this->Memoir->primaryKey => $id));
            $this->request->data = $this->Memoir->find('first', $options);
        }
        $capsules = $this->Memoir->Capsule->find('list');
        $this->set(compact('capsules'));
    }

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function delete($id = null) {
        $this->Memoir->id = $id;
        if (!$this->Memoir->exists()) {
            throw new NotFoundException(__('Invalid memoir'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->Memoir->delete()) {
            $this->Session->setFlash(__('The memoir has been deleted.'));
        } else {
            $this->Session->setFlash(__('The memoir could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }
}
