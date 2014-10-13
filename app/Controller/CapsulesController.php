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
    public $components = array('Paginator', 'RequestHandler');

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
        if (!$this->request->is('ajax')) {
            throw new MethodNotAllowedException(__('Invalid request'));
        }

        $this->layout = 'ajax';

        $this->Capsule->recursive = 0;
        // Add the virtual fields for favorite count and total rating
        $this->Capsule->virtualFields['favorite_count'] = Capsule::FIELD_FAVORITE_COUNT;
        $this->Capsule->virtualFields['total_rating'] = Capsule::FIELD_RATING;
        // Build the query options
        $query = array(
            'conditions' => array(
                'Capsule.user_id' => $this->Auth->user('id')
            ),
            'joins' => array(
                array(
                    'table' => 'discoveries',
                    'alias' => 'DiscoveryStat',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = DiscoveryStat.capsule_id',
                    )
                )
            ),
            'group' => array('Capsule.id')
        );
        // Search refinement
        $search = (isset($this->request->query['search']) && $this->request->query['search']) ? $this->request->query['search'] : "";
        if ($search) {
            $query['conditions']['Capsule.name LIKE'] = "%" . urldecode($search) . "%";
        }
        $this->Paginator->settings = $query;
        $this->set('capsules', $this->Paginator->paginate());
        $this->set(compact('search'));
    }

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
    public function view() {
        $this->autoRender = false;
        $this->layout = 'ajax';

        if (!$this->request->is('post') || !$this->request->is('ajax')) {
            throw new MethodNotAllowedException(__('Invalid request'));
        }

        if (!$this->Capsule->exists($this->request->data['id'])) {
            throw new NotFoundException(__('Invalid capsule'));
        }

        // Get the Capsule
        $capsule = $this->Capsule->find('first', array(
            'conditions' => array(
                'Capsule.id' => $this->request->data['id']
            )
        ));

        // Determine if the current User is the owner
        $isOwned = (isset($capsule['Capsule']['user_id']) && $capsule['Capsule']['user_id'] == $this->Auth->user('id')) ? true : false;
        // Determines if the Capsule is reachable
        $isReachable = false;
        // Determines if this is a new Discovery
        $isNewDiscovery = false;

        // Determine if it is a Discovery
        if (!$isOwned) {
            $discovery = $this->Capsule->Discovery->find('first', array(
                'conditions' => array(
                    'Discovery.capsule_id' => $this->request->data['id'],
                    'Discovery.user_id' => $this->Auth->user('id')
                )
            ));
            // If this is not a Discovery, see if it is range to be opened
            if (!$discovery) {
                if ($isReachable = $this->Capsule->isReachable(
                    $this->request->data['id'],
                    $this->request->data['lat'],
                    $this->request->data['lng'],
                    Configure::read('Capsule.Search.Radius')
                )) {
                    if ($discovery = $this->Capsule->Discovery->saveNew(
                        $this->request->data['id'],
                        $this->Auth->user('id')
                    )) {
                        $isNewDiscovery = true;
                        $this->Session->setFlash(__('Congratulations!  You have discovered a new Capsule!'));
                    } else {
                        $this->Session->setFlash(__('There was a problem opening the Capsule.  Please try again.'));
                    }
                } else {
                    $this->Session->setFlash(__('Sorry, you are not within range to open this Capsule.'));
                }
            }
        } else {
            // This Capsule is owned by the current User
            $discovery = null;
        }

        // Render the view
        $view = new View($this, false);
        $view->set(compact('isOwned', 'isReachable', 'capsule', 'discovery'));

        // Build the response body
        $body = array(
            'view' => $view->render('view')
        );
        // Append the Capsule id if this is a new Discovery
        if ($isNewDiscovery) {
            $body['newDiscovery'] = array(
                'id' => $capsule['Capsule']['id'],
                'lat' => $capsule['Capsule']['lat'],
                'lng' => $capsule['Capsule']['lng'],
                'name' => $capsule['Capsule']['name']
            );
        }
        // Send the response
        $this->response->body(json_encode($body));
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
        $this->layout = 'ajax';

        if ($id && (!$this->Capsule->exists($id) || !$this->Capsule->ownedBy($this->Auth->user('id'), $id))) {
            throw new NotFoundException(__('Invalid capsule'));
        }

        if ($this->request->is(array('post', 'put'))) {
            // Handle some Capsule data here to prevent form tampering
            $fieldList = array(
                'Capsule' => array(
                    'name',
                    'point',
                    'user_id'
                )
            );
            if ($id) {
                $this->request->data['Capsule']['id'] = $id;
                unset($this->request->data['Capsule']['lat']);
                unset($this->request->data['Capsule']['lng']);
            } else {
                $fieldList = array_merge_recursive($fieldList, array(
                    'Capsule' => array(
                        'lat', 'lng'
                    )
                ));
            }

            if ($this->Capsule->saveDiff($this->request->data, array(
                'deep' => true, 'fieldList' => $fieldList,
                'removeHasMany' => 'Memoir', 'associateOwner' => true, 'updateCtagForUser' => $this->Auth->user('id')
            ))) {
                // Turn off autoRender
                $this->autoRender = false;

                // Flash message
                $this->Session->setFlash(__('The capsule has been saved.'));

                // Determine the ID of the INSERTed/UPDATEd Capsule
                $capsuleId;
                if ($id) {
                    $capsuleId = $id;
                } else {
                    $capsuleId = $this->Capsule->getLastInsertID();
                }

                // Get the Capsule
                $capsule = $this->Capsule->find('first', array(
                    'conditions' => array(
                        'Capsule.id' => $capsuleId
                    )
                ));

                // Build the response body
                $body = array(
                    'capsule' => array(
                        'isNew' => ($id) ? false : true,
                        'id' => $capsule['Capsule']['id'],
                        'lat' => $capsule['Capsule']['lat'],
                        'lng' => $capsule['Capsule']['lng'],
                        'name' => $capsule['Capsule']['name']
                    )
                );
                // Send the response
                $this->response->body(json_encode($body));
                return;
            } else {
                $this->response->statusCode(400);
                $this->Session->setFlash(__('The capsule could not be saved. Please, try again.'));
            }
        } else {
            if ($id) {
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
        }
        // Use the add view
        $this->render('add');
    }

/**
 * delete method
 *
 * @param string $id
 */
    public function delete($id = null) {
        // Do not render a view
        $this->autoRender = false;
        $this->layout = false;

        $this->Capsule->id = $id;
        if (!$this->Capsule->exists() || !$this->Capsule->ownedBy($this->Auth->user('id'))) {
            $this->response->statusCode(404);
        } else {
            $this->request->allowMethod('post', 'delete');
            if ($this->Capsule->delete()) {
                $this->response->statusCode(204);
            } else {
                $this->response->statusCode(500);
            }
        }
    }

/**
 * map method
 *
 * Displays the map
 *
 * @return void
 */
    public function map() {
    
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
