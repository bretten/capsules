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
        'rating' => array(
            'decimal' => array(
                'rule' => array('decimal', 1),
                'message' => 'Please enter a valid rating.'
            )
        )
    );

/**
 * Creates a Discovery given a Capsule and User.
 *
 * @param $capsuleId
 * @param $userId
 * @return mixed
 */
    public function create($capsuleId, $userId) {
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
