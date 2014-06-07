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
        'title' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a title.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength' ,255),
                'message' => 'The title cannot exceed 255 characters.'
            )
        ),
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
