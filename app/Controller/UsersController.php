<?php
App::uses('AppController', 'Controller');

/**
 * Users Controller
 *
 * @property User $User
 * @property ApiComponent $Api
 */
class UsersController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Api');

    /**
     * beforeFilter method
     *
     * @return void
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login'));
    }

    /**
     * Allows a User to login
     *
     * @return void
     */
    public function login() {
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash(__('Invalid credentials.'), 'notification',
                    array('class' => 'alert-danger', 'dismissible' => true));
            }
        }
    }

    /**
     * Allows a User to logout
     *
     * @return void
     */
    public function logout() {
        $this->redirect($this->Auth->logout());
    }

    /**
     * Lets a User update their account
     *
     * @throws NotFoundException
     */
    public function account() {
        $this->User->id = $this->Auth->user('id');
        if (!$this->User->exists()) {
            throw new NotFoundException();
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->User->save($this->request->data, array(
                'confirmPassword' => (boolean)$this->request->data['User']['change_password'],
                'fieldList' => array(
                    'password', 'email', 'confirm_password'
                )))
            ) {
                $this->Session->setFlash(__('Your account has been saved.'), 'notification',
                    array('class' => 'alert-success', 'dismissible' => true));
                $this->redirect(array('action' => 'account'));
            } else {
                $this->Session->setFlash(__('Your account could not be saved. Please, try again.'), 'notification',
                    array('class' => 'alert-danger', 'dismissible' => true));
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
     * Views the profile of the specified User
     *
     * @param string $username The User to view
     */
    public function view($username = "") {
        $this->Api->getUser($this->User->getIdByUsername($username));
    }

}
