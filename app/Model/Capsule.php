<?php
App::uses('AppModel', 'Model');
/**
 * Capsule Model
 *
 * @property User $User
 * @property Discovery $Discovery
 * @property Memoir $Memoir
 */
class Capsule extends AppModel {

/**
 * Display field
 *
 * @var string
 */
    public $displayField = 'name';

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
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );

/**
 * hasMany associations
 *
 * @var array
 */
    public $hasMany = array(
        'Discovery' => array(
            'className' => 'Discovery',
            'foreignKey' => 'capsule_id',
            'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        ),
        'Memoir' => array(
            'className' => 'Memoir',
            'foreignKey' => 'capsule_id',
            'dependent' => true,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        )
    );

/**
 * validate
 *
 * @var array
 */
    public $validate = array(
        'name' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a name.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength' ,255),
                'message' => 'The name cannot exceed 255 characters.'
            )
        ),
        'lat' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a latitude.',
                'required' => true
            ),
            'decimal' => array(
                'rule' => array('decimal', 6),
                'message' => 'Please enter a valid latitude.'
            )
        ),
        'lng' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a longitude.',
                'required' => true
            ),
            'decimal' => array(
                'rule' => array('decimal', 6),
                'message' => 'Please enter a valid longitude.'
            )
        )
    );

}
