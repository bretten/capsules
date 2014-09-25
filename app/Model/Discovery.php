<?php
App::uses('AppModel', 'Model');
/**
 * Discovery Model
 *
 * @property Capsule $Capsule
 * @property User $User
 */
class Discovery extends AppModel {

/**
 * actsAs
 *
 * @var array
 */
    public $actsAs = array(
        'Belongs' => array(
            'userPrimaryKey' => 'id',
            'foreignKey' => 'user_id'
        ),
        'Resource' => array(
            'etagField' => 'etag',
            'autoSave' => true
        )
    );

/**
 * belongsTo associations
 *
 * @var array
 */
    public $belongsTo = array(
        'Capsule' => array(
            'className' => 'Capsule',
            'foreignKey' => 'capsule_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

/**
 * validate
 *
 * @var array
 */
    public $validate = array(
        'favorite' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                'message' => 'Please choose either favorite or unfavorite.'
            )
        ),
        'rating' => array(
            'range' => array(
                'rule' => array('inList', array(-1, 0, 1, '-1', '0', '1')),
                'message' => 'Please enter a valid rating.'
            )
        )
    );

/**
 * After save callback
 *
 * @param boolean $created INSERT or UPDATE
 * @param array $options Options passed from Model::save().
 */
    public function afterSave($created, $options = array()) {
        // Update the Capsule ctag for the User owner
        if (isset($options['updateCtagForUser']) && $this->User->exists($options['updateCtagForUser'])) {
            $this->User->updateCtag('ctag_discoveries', $options['updateCtagForUser']);
        }
    }

/**
 * Before delete callback
 *
 * @param boolean $cascade
 * @return void
 */
    public function beforeDelete($cascade = true) {
        // Update the Capsule ctag for the User
        if (isset($this->id)) {
            // Get the User id
            $userId = $this->field('user_id', array('Discovery.id' => $this->id));
            $this->User->updateCtag('ctag_discoveries', $userId);
        }
    }

/**
 * INSERTs a new Discovery given a Capsule and User.
 *
 * @param $capsuleId
 * @param $userId
 * @return mixed
 */
    public function saveNew($capsuleId, $userId) {
        $data = array(
            'Discovery' => array(
                'capsule_id' => $capsuleId,
                'user_id' => $userId
            )
        );

        return $this->save($data);
    }

/**
 * Checks if a User has already discovered a Capsule.
 *
 * @param $capsuleId
 * @param $userId
 * @return bool
 */
    public function created($capsuleId, $userId) {
        return $this->find('first', array(
            'conditions' => array(
                'Discovery.capsule_id' => $capsuleId,
                'Discovery.user_id' => $userId
            ),
            'recursive' => -1,
            'callbacks' => false
        ));
    }

}
