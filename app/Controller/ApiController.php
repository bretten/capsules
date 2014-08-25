<?php
App::uses('AppController', 'Controller');
/**
 * API Controller
 *
 * @property PaginatorComponent $Paginator
 */
class ApiController extends AppController {

/**
 * Components
 *
 * @var array
 */
    public $components = array('Paginator');

/**
 * uses
 *
 * @var array
 */
    public $uses = array('Capsule', 'Discovery');

/**
 * beforeFilter method
 *
 * @return void
 */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->autoRender = false;
        $this->layout = false;
    }

/**
 * API method to retrieve a collection's ctag
 *
 * @param string $collection
 * @return void
 */
    public function ctag($collection = null) {
        $body = array();

        if ($this->request->is('get')) {
            // Will hold the ctag
            $ctag;
            // The collection
            switch ($collection) {
                case "capsules":
                    $ctag = $this->Capsule->User->field('ctag_capsules', array('User.id' => $this->StatelessAuth->user('id')));
                    break;
                case "discoveries":
                    $ctag = $this->Discovery->User->field('ctag_discoveries', array('User.id' => $this->StatelessAuth->user('id')));
                    break;
                default:
                    $ctag = '';
                    $this->response->statusCode(404);
                    break;
            }
            // Build the response body
            if ($ctag) {
                $body['data'] = array(
                    'ctag' => $ctag
                );
                $this->response->statusCode(200);
            }
        } else {
            $this->response->statusCode(405);
        }

        $this->response->body(json_encode($body));
    }

/**
 * API method to retrieve etags for a collection
 *
 * @param string $collection
 * @return void
 */
    public function status($collection = null) {
        $body = array();

        if ($this->request->is('get')) {
            // The collection
            switch ($collection) {
                case "capsules":
                    $capsules = $this->Capsule->find('all', array(
                        'conditions' => array(
                            'Capsule.user_id' => $this->StatelessAuth->user('id')
                        ),
                        'fields' => array('Capsule.id', 'Capsule.etag')
                    ));
                    $body = Hash::map($capsules, "{n}.Capsule", function($data) {
                        return array(
                            'data' => array(
                                'id' => $data['id'],
                                'etag' => $data['etag']
                            )
                        );
                    });
                    break;
                case "discoveries":
                    $discoveries = $this->Discovery->find('all', array(
                        'conditions' => array(
                            'Discovery.user_id' => $this->StatelessAuth->user('id')
                        ),
                        'fields' => array('Discovery.capsule_id', 'Discovery.etag')
                    ));
                    $body = Hash::map($discoveries, "{n}.Discovery", function($data) {
                        return array(
                            'data' => array(
                                'id' => $data['capsule_id'],
                                'etag' => $data['etag']
                            )
                        );
                    });
                    break;
                default:
                    $this->response->statusCode(404);
                    break;
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

/**
 * API method to retrieve data for specified resources
 *
 * @param string $collection
 * @return void
 */
    public function report($collection = null) {
        $body = array();

        if ($this->request->is('post')) {
            if (!isset($this->request->data['id']) || !is_array($this->request->data['id'])) {
                $this->response->statusCode(400);
            } else {
                // The collection
                switch ($collection) {
                    case "capsules":
                        $capsules = $this->Capsule->find('all', array(
                            'conditions' => array(
                                'Capsule.id' => $this->request->data['id'],
                                'Capsule.user_id' => $this->StatelessAuth->user('id')
                            ),
                            'fields' => array('Capsule.id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng', 'Capsule.etag')
                        ));
                        $body = Hash::map($capsules, "{n}.Capsule", function($data) {
                            return array(
                                'data' => $data
                            );
                        });
                        break;
                    case "discoveries":
                        $discoveries = $this->Discovery->find('all', array(
                            'conditions' => array(
                                'Discovery.capsule_id' => $this->request->data['id'],
                                'Discovery.user_id' => $this->StatelessAuth->user('id')
                            ),
                            'fields' => array('Discovery.capsule_id', 'Discovery.favorite', 'Discovery.rating', 'Discovery.etag')
                        ));
                        $body = Hash::map($discoveries, "{n}.Discovery", function($data) {
                            return array(
                                'data' => array(
                                    'id' => $data['capsule_id'],
                                    'favorite' => $data['favorite'],
                                    'rating' => $data['rating'],
                                    'etag' => $data['etag']
                                )
                            );
                        });
                        break;
                    default:
                        $this->response->statusCode(404);
                        break;
                }
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

/**
 * API method to retrieve undiscovered Capsules in the User's radius.
 *
 * @return void
 */
    public function ping() {
        $body = array();

        if ($this->request->is('post')) {
            if (!isset($this->request->data['lat']) || !is_numeric($this->request->data['lat'])
                || !isset($this->request->data['lng']) || !is_numeric($this->request->data['lng'])
            ) {
                $this->response->statusCode(400);
            } else {
                $capsules = $this->Capsule->getUndiscovered(
                    $this->StatelessAuth->user('id'),
                    $this->request->data['lat'],
                    $this->request->data['lng'],
                    Configure::read('Capsule.Search.Radius')
                );
                $body = Hash::map($capsules, "{n}.Capsule", function($data) {
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

/**
 * API function to "open"/"discover" a Capsule.
 *
 * @return void
 */
    public function open() {
        $body = array();

        if ($this->request->is('post')) {
            if (!isset($this->request->data['id'])
                || !isset($this->request->data['lat']) || !is_numeric($this->request->data['lat'])
                || !isset($this->request->data['lng']) || !is_numeric($this->request->data['lng'])
            ) {
                $this->response->statusCode(400);
            } elseif (!$this->Capsule->exists($this->request->data['id'])) {
                $this->response->statusCode(404);
            } else {
                if ($exists = $this->Capsule->Discovery->created(
                    $this->request->data['id'],
                    $this->StatelessAuth->user('id')
                )) {
                    $body['data'] = array(
                        'etag' => $exists['Discovery']['etag']
                    );
                } else {
                    if ($this->Capsule->isReachable(
                        $this->request->data['id'],
                        $this->request->data['lat'],
                        $this->request->data['lng'],
                        Configure::read('Capsule.Search.Radius')
                    )) {
                        if ($insert = $this->Capsule->Discovery->saveNew(
                            $this->request->data['id'],
                            $this->StatelessAuth->user('id')
                        )) {
                            $body['data'] = array(
                                'etag' => $insert['Discovery']['etag']
                            );
                        } else {
                            $this->response->statusCode(500);
                        }
                    } else {
                        $this->response->statusCode(403);
                    }
                }
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

/**
 * API method to handle POST, GET, DELETE on a single Capsule
 *
 * @return void
 */
    public function capsule($id = null) {
        $body = array();

        if ($this->request->is('post')) {
            $this->request->data['user_id'] = $this->StatelessAuth->user('id');
            if ($result = $this->Capsule->saveDiff($this->request->data, array(
                'deep' => true, 'removeHasMany' => 'Memoir', 'updateCtagForUser' => $this->StatelessAuth->user('id')
            ))) {
                // Determine the Capsule id
                $updateId = 0;
                if (isset($this->request->data['id'])) { // UPDATE
                    $updateId = $this->request->data['id'];
                } else { // INSERT
                    $updateId = $this->Capsule->getLastInsertId();
                }
                // Get the new Capsule data
                $capsule = $this->Capsule->find('first', array(
                    'conditions' => array(
                        'Capsule.id' => $updateId,
                        'Capsule.user_id' => $this->StatelessAuth->user('id')
                    ),
                    'fields' => array('Capsule.id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng', 'Capsule.etag')
                ));
                if ($capsule && isset($capsule['Capsule'])) {
                    $body['data'] = $capsule['Capsule'];
                } else {
                    $this->response->statusCode(500);
                }
            } else {
                $this->response->statusCode(500);
            }
        } elseif ($this->request->is('get')) {
            $this->response->statusCode(501);
        } elseif ($this->request->is('delete')) {
            $this->response->statusCode(501);
        } else {
            $this->response->statusCode(405);
        }

        // Indicate success if the response body was built
        if ($body) {
            $this->response->statusCode(200);
        }

        $this->response->body(json_encode($body));
    }

/**
 * API method to handle POST, GET, DELETE on a single Discovery
 *
 * @return void
 */
    public function discovery($id = null) {
        $body = array();

        if ($this->request->is('post')) {
            if (!isset($this->request->data['id'])) {
                $this->response->statusCode(400);
            } elseif (!$exists = $this->Capsule->Discovery->created(
                    $this->request->data['id'],
                    $this->StatelessAuth->user('id')
            )) {
                $this->response->statusCode(404);
            } else {
                $this->request->data['id'] = $exists['Discovery']['id'];
                if ($result = $this->Discovery->save($this->request->data, array('updateCtagForUser' => $this->StatelessAuth->user('id')))) {
                    $body['data'] = array(
                        'etag' => $result['Discovery']['etag']
                    );
                } else {
                    $this->response->statusCode(500);
                }
            }
        } elseif ($this->request->is('get')) {
            $this->response->statusCode(501);
        } elseif ($this->request->is('delete')) {
            $this->response->statusCode(501);
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