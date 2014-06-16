<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 */
class UsersController extends AppController {

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
        // Use Basic authentication for authenticating API calls
        if ($this->request->params['action'] === 'authenticate') {
            $this->Auth->authenticate = array('Basic');
        }
        $this->Auth->allow(array('login'));
    }

/**
 * login method
 *
 * @return void
 */
    public function login() {
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash(__('Invalid credentials.'));
            }
        }
    }

/**
 * logout method
 *
 * @return void
 */
    public function logout() {
        $this->redirect($this->Auth->logout());
    }


/**
 * account method
 *
 * @throws NotFoundException
 * @return void
 */
    public function account() {
        $this->User->id = $this->Auth->user('id');
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->User->save($this->request->data, array(
                'confirmPassword' => (boolean) $this->request->data['User']['change_password'],
                'fieldList' => array(
                    'password', 'email', 'confirm_password'
                )
            ))) {
                $this->Session->setFlash(__('Your account has been saved.'));
                return $this->redirect(array('action' => 'account'));
            } else {
                $this->Session->setFlash(__('Your account could not be saved. Please, try again.'));
            }
        } else {
            $options = array(
                'conditions' => array(
                    'User.' . $this->User->primaryKey => $this->Auth->user('id')
                )
            );
            $this->request->data = $this->User->find('first', $options);
        }
    }

/**
 * index method
 *
 * @return void
 */
    public function index() {
        $this->User->recursive = 0;
        $this->set('users', $this->Paginator->paginate());
    }

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function view($id = null) {
        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }
        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $this->set('user', $this->User->find('first', $options));
    }

/**
 * add method
 *
 * @return void
 */
    public function add() {
        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
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
        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
            $this->request->data = $this->User->find('first', $options);
        }
    }

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function delete($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->User->delete()) {
            $this->Session->setFlash(__('The user has been deleted.'));
        } else {
            $this->Session->setFlash(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * API method to handle authenticating Users.  Response body contains an authentication token
     * to be used in future API calls.
     *
     * @return void
     */
    public function authenticate() {
        $this->autoRender = false;
        $this->layout = false;

        $body = array(
            'success' => false
        );

        if ($this->Auth->login()) {
            // Create the User's token
            $token = Security::hash(uniqid() . 'capsules' . $this->Auth->user('id'), null, true);
            // Save the User's token
            $data = array(
                'id' => $this->Auth->user('id'),
                'token' => $token
            );
            if ($this->User->save($data, array('fieldList' => array('id', 'token')))) {
                $body = array(
                    'success' => true,
                    'token' => $token
                );
            }
        }

        $this->response->body(json_encode($body));
    }

}
