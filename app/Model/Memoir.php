<?php
App::uses('AppModel', 'Model');
/**
 * Memoir Model
 *
 * @property Capsule $Capsule
 */
class Memoir extends AppModel {

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
