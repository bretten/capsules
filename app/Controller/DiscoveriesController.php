<?php
App::uses('AppController', 'Controller');

/**
 * Discoveries Controller
 *
 * @property Discovery $Discovery
 * @property ApiComponent $Api
 */
class DiscoveriesController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Api', 'RequestHandler');

    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = array('Js');

    /**
     * Lists Capsules discovered by the current User
     *
     * @return void
     */
    public function index() {
        // Get the Capsules
        $capsules = $this->Discovery->Capsule->getDiscoveredForUser($this->Auth->user('id'), null, null, null, null,
            array(
                'includeCapsuleOwner' => true,
                'includeMemoirs' => true,
                'page' => 1,
                'limit' => ApiComponent::$objectLimit,
                'order' => \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                    \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC)
            ));
        $this->set('capsules', $capsules);
    }

}
