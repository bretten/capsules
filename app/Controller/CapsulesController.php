<?php
App::uses('AppController', 'Controller');

/**
 * Capsules Controller
 *
 * @property Capsule $Capsule
 * @property ApiComponent $Api
 */
class CapsulesController extends AppController {

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
     * Lists Capsules belonging to the current User
     *
     * @return void
     */
    public function index() {
        // Get the Capsules
        $capsules = $this->Capsule->getForUser($this->Auth->user('id'), null, null, null, null, array(
            'includeCapsuleOwner' => true,
            'includeMemoirs' => true,
            'page' => 1,
            'limit' => ApiComponent::$objectLimit,
            'order' => \Capsules\Http\RequestContract::getCapsuleOrderBySortKey(
                \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC)
        ));
        $this->set('capsules', $capsules);
    }

    /**
     * Allows User to create a new Capsule
     *
     * @return void
     */
    public function add() {

    }

    /**
     * Displays the Map and pre-loads the ctags and Capsule collections
     *
     * @return void
     */
    public function map() {
        // Get the ctags
        $ctagCapsules = $this->Capsule->User->getCtagCapsules($this->Auth->user('id'));
        $ctagDiscoveries = $this->Capsule->User->getCtagDiscoveries($this->Auth->user('id'));
        // Get the Capsules
        $capsules = $this->Capsule->getForUser($this->Auth->user('id'), null, null, null, null, array(
            'includeCapsuleOwner' => true
        ));
        $capsules = Hash::combine($capsules, "{n}.Capsule.id", "{n}");
        $capsules = json_encode($capsules);
        // Get the Discoveries
        $discoveries = $this->Capsule->getDiscoveredForUser($this->Auth->user('id'), null, null, null, null, array(
            'includeCapsuleOwner' => true
        ));
        $discoveries = Hash::combine($discoveries, "{n}.Capsule.id", "{n}");
        $discoveries = json_encode($discoveries);

        // See if a focus location or Capsule was passed in
        $lat = isset($this->request->query['lat']) ? $this->request->query['lat'] : null;
        $lng = isset($this->request->query['lng']) ? $this->request->query['lng'] : null;
        $focusType = isset($this->request->query['type']) ? $this->request->query['type'] : null;
        $focusId = isset($this->request->query['id']) ? $this->request->query['id'] : null;

        $this->set('ctagCapsules', $ctagCapsules);
        $this->set('ctagDiscoveries', $ctagDiscoveries);
        $this->set('capsules', $capsules);
        $this->set('discoveries', $discoveries);
        $this->set('lat', $lat);
        $this->set('lng', $lng);
        $this->set('focusType', $focusType);
        $this->set('focusId', $focusId);
    }

}
