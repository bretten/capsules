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
            'notBlank' => array(
                'rule' => 'notBlank',
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
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a password.',
            ),
            'minLength' => array(
                'rule' => array('minLength', 8),
                'message' => 'Please ensure your password is 8 or more characters.',
            )
        )
    );

    /**
     * The field list for saving a new User
     *
     * @var array
     */
    public $fieldListCreate = array(
        'username', 'password', 'email', 'confirm_password', 'token'
    );

    /**
     * beforeSave
     *
     * @param array $options Options passed from Model::save().
     * @return bool bool True if the operation should continue, false if it should abort
     */
    public function beforeSave($options = array()) {
        if (isset($options['assignNewAuthToken']) && $options['assignNewAuthToken'] === true) {
            $this->data = $this->appendNewAuthTokenToData($this->data);
        }
        return true;
    }

    /**
     * Gets the authentication token for the specified User
     *
     * @param mixed $id The ID of the User to get the authentication token for
     * @return string The authentication token
     */
    public function getAuthTokenForUser($id) {
        $this->id = $id;

        return $this->field('token');
    }

    /**
     * Saves a new authentication token for the specified User
     *
     * @param mixed $userId The ID of the User to set a new authentication token for
     * @return array|bool The result of the save
     */
    public function setNewAuthToken($userId) {
        // Set the ID
        $this->id = $userId;
        // Save
        return $this->saveField('token', \Capsules\Authentication\Token::instance()->getTokenString());
    }

    /**
     * Updates the specified ctag field for the given User.
     *
     * @param string $ctag
     * @param int $userId
     * @return boolean
     */
    public function updateCtag($ctag, $userId) {
        return $this->updateAll(
            array('User.' . $ctag => "'" . md5(time()) . "'"),
            array('User.id' => $userId)
        );
    }

    /**
     * Appends a new authentication token to the data array
     *
     * @param array $data The data array to append the authentication token to
     * @return array The updated data array
     */
    private function appendNewAuthTokenToData($data = array()) {
        $token = \Capsules\Authentication\Token::instance()->getTokenString();
        if (isset($data[$this->alias])) {
            $data[$this->alias]['token'] = $token;
        } else {
            $data['token'] = $token;
        }

        return $data;
    }

}
