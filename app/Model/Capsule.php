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
 * Database function for calculating the number of Discoveries
 *
 * @const string
 */
    const FIELD_DISCOVERY_COUNT = 'count(DiscoveryStat.capsule_id)';

/**
 * Database function for calculating the number of Discovery favorites on a Capsule
 *
 * @const string
 */
    const FIELD_FAVORITE_COUNT = 'sum(if(DiscoveryStat.favorite >= 1, 1, 0))';

/**
 * Database function for calculating the total Discovery rating on a Capsule
 *
 * @const string
 */
    const FIELD_RATING = 'sum(
        CASE
            WHEN DiscoveryStat.rating >= 1 THEN 1
            WHEN DiscoveryStat.rating <= -1 THEN -1
            ELSE 0
        END
    )';

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
                'rule' => array('decimal'),
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
                'rule' => array('decimal'),
                'message' => 'Please enter a valid longitude.'
            )
        )
    );

/**
 * Before save method
 *
 * @param array $options Options passed from Model::save().
 * @return boolean true to continue, false to abort the save
 */
    public function beforeSave($options = array()) {
        // Use the latitude and longitude values in a MySQL expression to save a POINT data type
        if (isset($this->data[$this->alias]['lat']) && isset($this->data[$this->alias]['lng'])) {
            $dataSource = $this->getDataSource();
            $this->data[$this->alias]['point'] = $dataSource->expression("POINT(" . $this->data[$this->alias]['lat'] . ", " . $this->data[$this->alias]['lng'] . ")");
        } elseif (isset($this->data['lat']) && isset($this->data['lng'])) {
            $dataSource = $this->getDataSource();
            $this->data['point'] = $dataSource->expression("POINT(" . $this->data['lat'] . ", " . $this->data['lng'] . ")");
        }
        return true;
    }

/**
 * After save callback
 *
 * @param boolean $created INSERT or UPDATE
 * @param array $options Options passed from Model::save().
 */
    public function afterSave($created, $options = array()) {
        // Update the Capsule ctag for the User owner
        if (isset($options['updateCtagForUser']) && $this->User->exists($options['updateCtagForUser'])) {
            $this->User->updateCtag('ctag_capsules', $options['updateCtagForUser']);
        }
    }

/**
 * Before delete callback
 *
 * @param boolean $cascade
 * @return void
 */
    public function beforeDelete($cascade = true) {
        // Update the Capsule ctag for the User
        if (isset($this->id)) {
            // Get the User id
            $userId = $this->field('user_id', array('Capsule.id' => $this->id));
            $this->User->updateCtag('ctag_capsules', $userId);
        }
    }

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
 * Returns all Capsules within the bounded rectangle
 *
 * @param float $latNE The Northeast latitude
 * @param float $lngNE The Northeast longitude
 * @param float $latSW The Southwest latitude
 * @param float $lngSW The Southwest longitude
 * @param array $query
 * @return array
 */
    public function getInRectangle($latNE, $lngNE, $latSW, $lngSW, $query = array()) {
        $append = array(
            'conditions' => array(
                "MBRWITHIN(Capsule.point, MULTIPOINT(POINT($latNE, $lngNE), POINT($latSW, $lngSW)))"
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->find('all', $query);
    }

/**
 * Returns all Discovery Capsules within the bounded rectangle
 *
 * TODO: Rework without using sub-query.
 *
 * @param float $latNE The Northeast latitude
 * @param float $lngNE The Northeast longitude
 * @param float $latSW The Southwest latitude
 * @param float $lngSW The Southwest longitude
 * @param array $query
 * @return array
 */
    public function getDiscovered($userId, $latNE, $lngNE, $latSW, $lngSW, $query = array()) {
        $append = array(
            'joins' => array(
                array(
                    'table' => '(SELECT * FROM discoveries WHERE discoveries.user_id = ' . $userId . ')',
                    'alias' => 'Discovery',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Capsule.id = Discovery.capsule_id'
                    ),
                )
            ),
            'fields' => array(
                'Capsule.*', 'Discovery.*'
            ),
            'conditions' => array(
                'Discovery.user_id' => $userId
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->getInRectangle($latNE, $lngNE, $latSW, $lngSW, $query);
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
