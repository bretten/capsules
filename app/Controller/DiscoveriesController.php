<?php
App::uses('AppController', 'Controller');

/**
 * Discoveries Controller
 *
 * @property Discovery $Discovery
 * @property ApiComponent $Api
 * @property PaginatorComponent $Paginator
 */
class DiscoveriesController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Api', 'Paginator', 'RequestHandler', 'PaginatorBounding');

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
        // Get the Capsules
        $capsules = $this->Discovery->Capsule->getDiscoveredForUser($this->Auth->user('id'), null, null, null, null,
            array(
                'includeDiscoveryStats' => true,
                'includeMemoirs' => true,
                'page' => 1,
                'limit' => ApiComponent::$objectLimit,
                'order' => \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                    \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_UNOPENED_THEN_NAME_ASC)
            ));
        $this->set('capsules', $capsules);
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
            if ($this->Discovery->save($this->request->data, array('updateCtagForUser' => $this->Auth->user('id')))) {
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
            if ($this->Discovery->save($this->request->data, array('updateCtagForUser' => $this->Auth->user('id')))) {
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

    /**
     * Internal API method to rate a Discovery
     *
     * @return void
     */
    public function rate() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        $body = array();

        if ($this->request->is('post') && $this->request->is('ajax')) {
            $discovery = $this->Discovery->find('first', array(
                'conditions' => array(
                    'Discovery.id' => $this->request->data['id'],
                    'Discovery.user_id' => $this->Auth->user('id')
                )
            ));
            if (!$discovery) {
                $this->response->statusCode(404);
            } else {
                if ($discovery['Discovery']['rating'] != 0 && $discovery['Discovery']['rating'] == $this->request->data['rating']) {
                    $this->request->data['rating'] = 0;
                }
                if ($discovery['Discovery']['rating'] == $this->request->data['rating'] || $this->Discovery->save($this->request->data)) {
                    $body['rating'] = $this->request->data['rating'];
                    $this->response->statusCode(200);
                } else {
                    $this->response->statusCode(500);
                }
            }
        } else {
            $this->response->statusCode(405);
        }

        $this->response->body(json_encode($body));
    }

    /**
     * Internal API method to favorite a Discovery
     *
     * @return void
     */
    public function favorite() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        $body = array();

        if ($this->request->is('post') && $this->request->is('ajax')) {
            $discovery = $this->Discovery->find('first', array(
                'conditions' => array(
                    'Discovery.id' => $this->request->data['id'],
                    'Discovery.user_id' => $this->Auth->user('id')
                )
            ));
            if (!$discovery) {
                $this->response->statusCode(404);
            } else {
                // Inverse the favorite flag
                $this->request->data['favorite'] = !$discovery['Discovery']['favorite'];

                if ($this->Discovery->save($this->request->data)) {
                    $body['favorite'] = $this->request->data['favorite'];
                    $this->response->statusCode(200);
                } else {
                    $this->response->statusCode(500);
                }
            }
        } else {
            $this->response->statusCode(405);
        }

        $this->response->body(json_encode($body));
    }

}
