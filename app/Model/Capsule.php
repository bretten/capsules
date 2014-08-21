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
        ),
        'Resource' => array(
            'etagField' => 'etag',
            'autoSave' => true
        ),
        'Has'
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
                'rule' => array('maxLength', 255),
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

/**
 * Returns all Capsules within the specified radius around the specified latitude and longitude.
 *
 * @param $lat float
 * @param $lng float
 * @param $radius float The radius to query within in miles.
 * @param array $query
 * @return array
 */
    public function getInRadius($lat, $lng, $radius, $query = array()) {
        $this->virtualFields['distance'] = "
        (
            (
                ACOS(
                    SIN($lat * PI() / 180) * SIN(Capsule.lat * PI() / 180) +
                    COS($lat * PI() / 180) * COS(Capsule.lat * PI() / 180) *
                    COS(($lng - Capsule.lng) * PI() / 180)
                ) * 180 / PI()
            ) * 60 * 1.1515
        )";

        $append = array(
            'conditions' => array(
                'Capsule.distance <=' => $radius
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->find('all', $query);
    }

/**
 * Retrieves all Capsules that have not been discovered by the specified User, within the specified radius
 * around the specified latitude and longitude.
 *
 * TODO: Rework without using sub-query.
 *
 * @param $userId
 * @param $lat
 * @param $lng
 * @param $radius
 * @param array $query
 * @return array
 */
    public function getUndiscovered($userId, $lat, $lng, $radius, $query = array()) {
        $append = array(
            'joins' => array(
                array(
                    'table' => '(SELECT * FROM discoveries WHERE discoveries.user_id = ' . $userId . ')',
                    'alias' => 'Discovery',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = Discovery.capsule_id'
                    ),
                )
            ),
            'fields' => array(
                'Capsule.*', 'Discovery.*'
            ),
            'conditions' => array(
                'Capsule.user_id !=' => $userId,
                'Discovery.id IS NULL'
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->getInRadius($lat, $lng, $radius, $query);
    }

/**
 * Checks if the Capsule specified by the primary key is within the specified radius originating from the
 * specified latitude and longitude.
 *
 * @param $id
 * @param $lat
 * @param $lng
 * @param $radius
 * @param array $query
 * @return bool
 */
    public function isReachable($id, $lat, $lng, $radius, $query = array()) {
        $append = array(
            'conditions' => array(
                'Capsule.id' => $id
            ),
            'fields' => array(
                'Capsule.id'
            )
        );

        $query = array_merge_recursive($query, $append);

        return (boolean)$this->getInRadius($lat, $lng, $radius, $query);
    }

}
