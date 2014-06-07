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

/**
 * validate
 *
 * @var array
 */
    public $validate = array(
        'file' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please choose a file.',
                'required' => true
            )
        ),
        'order' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'Please enter a valid numeric ordering.'
            )
        )
    );

}
