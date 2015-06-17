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
 * beforeFilter method
 *
 * @return void
 */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('image'));
    }

/**
 * Serves images after the User is authenticated and the Memoir is queried from the database
 *
 * @param $id ID of the Memoir
 */
    public function image($id) {
        $this->autoRender = false;
        $this->layout = false;
        // Make sure the User is authenticated
        if (!$this->Auth->user()) {
            $this->response->statusCode(401);
            return;
        }
        // Try to find the Memoir
        $memoir = $this->Memoir->find('first', array(
            'conditions' => array(
                'Memoir.id' => $id
            )
        ));
        // If it does not exist, indicate it was not found
        if (!$memoir) {
            $this->response->statusCode(404);
            return;
        }
        // Add headers to indicate an image is being served
        header("Content-Type: " . $memoir['Memoir']['file_type']);
        header("Content-Length: " . $memoir['Memoir']['file_size']);
        header("Last-Modified: " . date(DATE_RFC2822, strtotime($memoir['Memoir']['modified'])));
        // Set the image as the response body
        readfile($memoir['Memoir']['file_location'] . DS . $memoir['Memoir']['file_public_name']);
    }

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
