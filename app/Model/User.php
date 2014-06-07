<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Capsule $Capsule
 * @property Discovery $Discovery
 */
class User extends AppModel {

/**
 * actsAs
 *
 * @var array
 */
    public $actsAs = array(
        'User' => array(
            'passwordField' => 'password',
            'confirmPassword' => array(
                'field' => 'confirm_password',
                'ruleName' => 'confirmPassword',
                'ruleMessage' => 'Please make sure both passwords are the same.'
            )
        )
    );

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Capsule' => array(
			'className' => 'Capsule',
			'foreignKey' => 'user_id',
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
		'Discovery' => array(
			'className' => 'Discovery',
			'foreignKey' => 'user_id',
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
        'username' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a username.',
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This username is already in use.'
            )
        ),
        'email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Please use a valid e-mail address.',
                'required' => true
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This e-mail address is already in use.',
                'required' => true
            )
        ),
        'password' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Please enter a password.',
            ),
            'minLength' => array(
                'rule' => array('minLength', 8),
                'message' => 'Please ensure your password is 8 or more characters.',
            )
        )
    );

}
