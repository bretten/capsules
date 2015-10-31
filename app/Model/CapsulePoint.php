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

}
