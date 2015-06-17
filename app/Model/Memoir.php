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
                'rule' => array('maxLength', 255),
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
        'file_location' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a file location.',
                'required' => true
            )
        ),
        'file_public_name' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a file name.',
                'required' => true
            )
        ),
        'file_original_name' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a file name.',
                'required' => true
            )
        ),
        'file_type' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a file type.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength', 64),
                'message' => 'The title cannot exceed 64 characters.'
            )
        ),
        'file_size' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a file size.',
                'required' => true
            ),
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'Please enter a valid file size.'
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
