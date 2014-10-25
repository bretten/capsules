<?php
App::uses('AppModel', 'Model');
/**
 * CapsulePoint Model
 *
 * @property Capsule $Capsule
 */
class CapsulePoint extends AppModel {

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
        )
    );

/**
 * Checks if a Capsule already has a CapsulePoint.
 *
 * @param $capsuleId
 * @param $userId
 * @return bool
 */
    public function created($capsuleId) {
        return $this->find('first', array(
            'conditions' => array(
                'CapsulePoint.capsule_id' => $capsuleId
            ),
            'recursive' => -1,
            'callbacks' => false
        ));
    }

}
