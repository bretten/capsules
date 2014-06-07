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

}
