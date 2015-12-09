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
     * List of fields to be returned when querying the User table
     *
     * @var array
     */
    public $fieldListProjection = array(
        'User.id', 'User.username', 'User.created'
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
     * Gets the User corresponding to the specified ID
     *
     * @param mixed $id The ID of the User to retrieve
     * @return array|null The User data if a matching row is found, otherwise false
     */
    public function getById($id) {
        $query = array(
            'conditions' => array(
                'User.id' => $id
            ),
            'fields' => $this->fieldListProjection
        );
        // Append the User stats to the query
        $query = $this->appendUserStatsToQuery($id, $query);

        return $this->find('first', $query);
    }

    /**
     * Gets the ID corresponding to the specified username
     *
     * @param string $username The username to get the ID for
     * @return string The ID corresponding to the username
     */
    public function getIdByUsername($username = "") {
        return $this->field('id', array(
            'User.username' => $username
        ));
    }

    /**
     * Gets the specified User's collection tag for Capsules
     *
     * @param mixed $id The ID of the User
     * @return string The collection tag
     */
    public function getCtagCapsules($id) {
        $this->id = $id;

        return $this->field('ctag_capsules');
    }

    /**
     * Gets the specified User's collection tag for Discoveries
     *
     * @param mixed $id The ID of the User
     * @return string The collection tag
     */
    public function getCtagDiscoveries($id) {
        $this->id = $id;

        return $this->field('ctag_discoveries');
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

    /**
     * Determines the number of Capsules and Discoveries for the specified User
     *
     * @param mixed $userId The ID of the User
     * @param array $query
     * @return array
     */
    private function appendUserStatsToQuery($userId, $query = array()) {
        // Add virtual fields for the Capsule and Discovery counts
        $this->virtualFields['capsule_count'] = $this->Capsule->find('count', array(
            'conditions' => array(
                'Capsule.user_id' => $userId,
                'Capsule.deleted' => false
            )
        ));
        $this->virtualFields['discovery_count'] = "COUNT(Discovery.id)";
        // Add joins to the query that will count the number of Discovered Capsules
        $append = array(
            'joins' => array(
                array(
                    'table' => 'discoveries',
                    'alias' => 'Discovery',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Discovery.user_id' => $userId
                    )
                ),
                array(
                    'table' => 'capsules',
                    'alias' => 'Capsule',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Discovery.capsule_id = Capsule.id',
                    )
                )
            ),
            'fields' => array(
                'Discovery.id', 'User.capsule_count', 'User.discovery_count'
            ),
            'conditions' => array(
                'Capsule.deleted' => false
            )
        );

        return array_merge_recursive($query, $append);
    }

}
