<?php

App::uses('Controller', 'Controller');

/**
 * AppController that all other controllers extend
 */
class AppController extends Controller {

    /**
     * components
     *
     * @var array
     */
    public $components = array(
        'Session',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'capsules', 'action' => 'map'),
            'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
            'authorize' => array('Controller')
        )
    );

    /**
     * beforeFilter method
     *
     * @return void
     */
    public function beforeFilter() {
        parent::beforeFilter();
        // If the User is logged in, there is no need to access the following pages
        if ($this->Auth->user()
            && ($this->request->params['controller'] == 'users' && $this->request->params['action'] == 'login')
        ) {
            $this->redirect($this->Auth->loginRedirect);
        }
    }

    /**
     * isAuthorized method
     *
     * @param array $user
     * @return boolean
     */
    public function isAuthorized($user) {
        if ($user) {
            return true;
        }

        return false;
    }

}
