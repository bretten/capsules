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
        )
    );

/**
 * hasOne associations
 *
 * @var array
 */
    public $hasOne = array(
        'CapsulePoint' => array(
            'className' => 'CapsulePoint',
            'foreignKey' => 'capsule_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'dependent' => true
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
            ),
            'checkLowerBounds' => array(
                'rule' => array('comparison', '>=', -90),
                'message' => 'Please enter a valid latitude.'
            ),
            'checkUpperBounds' => array(
                'rule' => array('comparison', '<=', 90),
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
            ),
            'checkLowerBounds' => array(
                'rule' => array('comparison', '>=', -180),
                'message' => 'Please enter a valid longitude.'
            ),
            'checkUpperBounds' => array(
                'rule' => array('comparison', '<=', 180),
                'message' => 'Please enter a valid longitude.'
            )
        )
    );

/**
 * Before find method
 *
 * @param array $query The options for the query
 * @return mixed true to continue, false to abort, or a modified $query
 */
    public function beforeFind($query) {
        // Include the corresponding POINT data columns
        if (isset($query['includePoints']) && $query['includePoints'] === true) {
            $append = array(
                'joins' => array(
                    array(
                        'table' => 'capsule_points',
                        'alias' => 'CapsulePoint',
                        'type' => 'INNER',
                        'conditions' => array(
                            'Capsule.id = CapsulePoint.capsule_id'
                        )
                    )
                )
            );

            return array_merge_recursive($query, $append);
        }

        return true;
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
 * Capsules are filtered by excluding those outside a bounding box and then further filtered by
 * measuring the distance to each Capsule from the User's location to determine if it is within
 * the radius.
 *
 * @param $lat float
 * @param $lng float
 * @param $radius float The radius to query within in miles.
 * @param array $query
 * @return array
 */
    public function getInRadius($lat, $lng, $radius, $query = array()) {
        $degreeLength = Configure::read('Spatial.Latitude.DegreeLength');
        $minuteLength = Configure::read('Spatial.Latitude.MinuteLength');
        $scalar = Configure::read('Spatial.BoundingBox.Scalar');
        $this->virtualFields['distance'] = "
        (
            (
                ACOS(
                    SIN($lat * PI() / 180) * SIN(Capsule.lat * PI() / 180) +
                    COS($lat * PI() / 180) * COS(Capsule.lat * PI() / 180) *
                    COS(($lng - Capsule.lng) * PI() / 180)
                ) * 180 / PI()
            ) * 60 * {$minuteLength}
        )";

        $append = array(
            'includePoints' => true,
            'conditions' => array(
                "MBRContains(
                    LineString(
                        Point(
                            {$lat} + ({$scalar} * {$radius}) / {$degreeLength},
                            {$lng} + ({$scalar} * {$radius}) / ({$degreeLength} / COS(RADIANS({$lat})))
                        ),
                        Point(
                            {$lat} - ({$scalar} * {$radius}) / {$degreeLength},
                            {$lng} - ({$scalar} * {$radius}) / ({$degreeLength} / COS(RADIANS({$lat})))
                        )
                    ),
                    CapsulePoint.point
                )",
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
            'includePoints' => true,
            'conditions' => array(
                "MBRWITHIN(CapsulePoint.point, MULTIPOINT(POINT($latNE, $lngNE), POINT($latSW, $lngSW)))"
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->find('all', $query);
    }

/**
 * Returns all Discovery Capsules within the bounded rectangle
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
                    'table' => 'discoveries',
                    'alias' => 'Discovery',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Capsule.id = Discovery.capsule_id',
                        'Discovery.user_id' => $userId
                    )
                )
            )
        );

        $query = array_merge_recursive($query, $append);

        return $this->getInRectangle($latNE, $lngNE, $latSW, $lngSW, $query);
    }

/**
 * Retrieves all Capsules that have not been discovered by the specified User, within the specified radius
 * around the specified latitude and longitude.
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
                    'table' => 'discoveries',
                    'alias' => 'Discovery',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = Discovery.capsule_id',
                        'Discovery.user_id' => $userId
                    )
                )
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

/**
 * Saves a single Capsule along with its CapsulePoint and Memoirs
 *
 * @param array $data The data to save
 * @param array $options Save options
 * @return mixed True on success, otherwise false on failure
 */
    public function saveAllWithUploads($data = array(), $options = array()) {
        // Handle all Memoir uploads separately from database transaction to prevent locking longer than needed
        $memoirValidationErrors = array();
        if (isset($data['Memoir']) && is_array($data['Memoir'])) {
            foreach ($data['Memoir'] as $key => &$memoir) {
                // Make sure the file key exists
                if (!isset($memoir['file'])) {
                    $memoir['file'] = array();
                }
                // Validate and process the upload
                $fileData = $this->Memoir->handleImageUpload($memoir['file']);
                // Add the file data to the Memoir so it can be saved
                if ($fileData) {
                    $memoir = array_merge($memoir, $fileData);
                }
                // Add any errors to the corresponding Memoir error array
                $errors = $this->Memoir->getUploadValidationMessages();
                if (!empty($errors)) {
                    $memoirValidationErrors['Memoir'][$key]['file'] = $errors;
                }
            }
        }

        // Get the data source
        $dataSource = $this->getDataSource();
        // Begin the transaction
        $dataSource->begin();
        // Flag to determine if the transaction should be committed
        $commit = true;

        // Calculate the spatial data since Cake's saveAll escapes data
        $pointData = $dataSource->query(sprintf("SELECT POINT(%s, %s) as PointData", $data['Capsule']['lat'], $data['Capsule']['lng']));
        // Add the spatial data to be saved
        $data['CapsulePoint'] = array(
            'point' => $pointData[0][0]['PointData']
        );
        // Remove validation for the file input
        $this->Memoir->validator()->remove('file');
        // Save Capsule, Memoir, and CapsulePoint data
        if (!$result = $this->saveAll($data, $options)) {
            $commit = false;
        }
        // Update the Capsule ctag for the User owner
        if ($commit && isset($options['updateCtagForUser']) && $this->User->exists($options['updateCtagForUser'])) {
            if (!$this->User->updateCtag('ctag_capsules', $options['updateCtagForUser'])) {
                $commit = false;
            }
        }

        // Commit the transaction or roll it back
        if ($commit) {
            $dataSource->commit();
        } else {
            $dataSource->rollback();
            // Delete the uploaded files
            if (isset($data['Memoir']) && is_array($data['Memoir'])) {
                foreach ($data['Memoir'] as $memoir) {
                    if (isset($memoir['file_location']) && isset($memoir['file_public_name'])) {
                        unlink($memoir['file_location'] . DS . $memoir['file_public_name']);
                    }
                }
            }
            // Add any Memoir errors to the validationErrors array
            $this->validationErrors = Hash::merge($this->validationErrors, $memoirValidationErrors);
            // Remove validation messages that don't need to be shown publicly
            $this->validationErrors = Hash::remove($this->validationErrors, 'Memoir.{n}.file_location');
            $this->validationErrors = Hash::remove($this->validationErrors, 'Memoir.{n}.file_public_name');
            $this->validationErrors = Hash::remove($this->validationErrors, 'Memoir.{n}.file_original_name');
            $this->validationErrors = Hash::remove($this->validationErrors, 'Memoir.{n}.file_type');
            $this->validationErrors = Hash::remove($this->validationErrors, 'Memoir.{n}.file_size');
        }

        return $result;
    }

}
