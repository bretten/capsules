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
        'CakeDecorations.Belongs' => array(
            'userPrimaryKey' => 'id',
            'foreignKey' => 'user_id'
        ),
        'CakeDecorations.Resource' => array(
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
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a name.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength', 255),
                'message' => 'The name cannot exceed 255 characters.'
            )
        ),
        'lat' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
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
            'notBlank' => array(
                'rule' => 'notBlank',
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
     * Field list for validating a Capsule
     *
     * @var array
     */
    public $fieldListValidate = array(
        'Capsule' => array('name', 'point', 'user_id', 'etag', 'lat', 'lng'),
        'Memoir' => array('title')
    );

    /**
     * Field list for creating a new Capsule
     *
     * @var array
     */
    public $fieldListCreate = array(
        'Capsule' => array('name', 'point', 'user_id', 'etag', 'lat', 'lng'),
        'Memoir' => array(
            'title', 'capsule_id', 'message', 'file_location', 'file_public_name', 'file_original_name', 'file_type',
            'file_size'
        )
    );

    /**
     * Field list for updating a Capsule's stats
     *
     * @var array
     */
    public $fieldListUpdateStats = array(
        'Capsule' => array('discovery_count', 'favorite_count', 'total_rating', 'modified')
    );

    /**
     * List of fields to be returned when querying the Capsule table
     *
     * @var array
     */
    public $fieldListProjection = array(
        'Capsule.id', 'Capsule.user_id', 'Capsule.name', 'Capsule.lat', 'Capsule.lng', 'Capsule.created',
        'Capsule.discovery_count', 'Capsule.favorite_count', 'Capsule.total_rating'
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
            $query = $this->appendCapsulePointsToQuery($query);
        }
        // Include Capsule owner information
        if (isset($query['includeCapsuleOwner']) && $query['includeCapsuleOwner'] === true) {
            $query = $this->appendCapsuleOwnerToQuery($query);
        }
        // Include statistics related to a Capsule's Discoveries
        if (isset($query['includeDiscoveryStats']) && $query['includeDiscoveryStats'] === true) {
            $query = $this->appendDiscoveryStatsToQuery($query);
        }
        // Include Memoirs
        if (isset($query['includeMemoirs']) && $query['includeMemoirs'] === true) {
            $query = $this->appendMemoirsToQuery($query);
        }
        // Filter by search terms
        if (isset($query['searchString']) && $query['searchString']) {
            $query = $this->appendSearchToQuery($query['searchString'], $query);
        }

        return $query;
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
     * Overrides parent's exists() method. Checks to see if the Capsule row exists and that it has not been
     * soft-deleted
     *
     * @param mixed $id The ID of the Capsule to check
     * @return bool True if the Capsule exists and has not been soft-deleted, otherwise false
     */
    public function exists($id = null) {
        if ($id === null) {
            $id = $this->getID();
        }

        if ($id === false) {
            return false;
        }

        if ($this->useTable === false) {
            return false;
        }

        return (bool)$this->find('count', array(
            'conditions' => array(
                'Capsule.id' => $id,
                'Capsule.deleted' => false
            ),
            'recursive' => -1,
            'callbacks' => false
        ));
    }

    /**
     * Gets the Capsule for the given ID
     *
     * @param mixed $id The ID of the Capsule to retrieve
     * @return array|null The Capsule data if a matching row is found, otherwise null
     */
    public function getById($id) {
        $query = array(
            'includeCapsuleOwner' => true,
            'conditions' => array(
                'Capsule.id' => $id
            ),
            'contain' => array(
                'Memoir' => array(
                    'fields' => $this->Memoir->fieldListProjection
                )
            ),
            'fields' => $this->fieldListProjection
        );
        // Exclude Capsules that have been soft-deleted
        $query = $this->appendSoftDeleteExclusionToQuery($query);
        return $this->find('first', $query);
    }

    /**
     * Gets the Capsule given an ID only if it belongs to the specified User ID
     *
     * @param mixed $id The ID of the Capsule to retrieve
     * @param mixed $userId The ID of the User that should own the Capsule
     * @return array|null The Capsule data if a matching row is found, otherwise null
     */
    public function getByIdForUser($id, $userId) {
        $query = array(
            'includeCapsuleOwner' => true,
            'conditions' => array(
                'Capsule.id' => $id,
                'Capsule.user_id' => $userId
            ),
            'contain' => array(
                'Memoir' => array(
                    'fields' => $this->Memoir->fieldListProjection
                )
            ),
            'fields' => $this->fieldListProjection
        );
        // Exclude Capsules that have been soft-deleted
        $query = $this->appendSoftDeleteExclusionToQuery($query);
        return $this->find('first', $query);
    }

    /**
     * Gets all Capsules for the specified User.  If the two sets of coordinates are specified, only Capsules
     * that fall into the bounded area will be returned.
     *
     * @param mixed $userId The ID of the User to retrieve the Capsules for.
     * @param null $latNE
     * @param null $lngNE
     * @param null $latSW
     * @param null $lngSW
     * @param array $query
     * @return array|null
     */
    public function getForUser($userId, $latNE = null, $lngNE = null, $latSW = null, $lngSW = null, $query = array()) {
        // Append to the query so that only the User's Capsule's are retrieved
        $query = $this->appendBelongsToUserToQuery($userId, $query);
        // Exclude Capsules that have been soft-deleted
        $query = $this->appendSoftDeleteExclusionToQuery($query);
        // Specify the fields that will be returned in the query
        $query = $this->appendCapsuleProjectionToQuery($query);
        // If there are two sets of coordinates, then append the query parameters to get Capsules within bounded area
        if ($latNE != null && $lngNE != null && $latSW != null && $lngSW != null) {
            $query = $this->appendInRectangleToQuery($latNE, $lngNE, $latSW, $lngSW, $query);
        }
        return $this->find('all', $query);
    }

    /**
     * Gets all discovered Capsules for the specified User.  If the two sets of coordinates are specified, only Capsules
     * within the bounded area will be returned.
     *
     * @param mixed $userId The ID of the User to retrieve the discovered Capsules for.
     * @param null $latNE
     * @param null $lngNE
     * @param null $latSW
     * @param null $lngSW
     * @param array $query
     * @return array|null
     */
    public function getDiscoveredForUser($userId, $latNE = null, $lngNE = null, $latSW = null, $lngSW = null,
                                         $query = array()) {
        // Build query to get Discoveries for the specified User
        $query = $this->appendDiscoveriesForUserToQuery($userId, $query);
        // Exclude Capsules that have been soft-deleted
        $query = $this->appendSoftDeleteExclusionToQuery($query);
        // Specify the fields that will be returned in the query
        $query = $this->appendCapsuleProjectionToQuery($query);
        $query = $this->appendDiscoveryProjectionToQuery($query);
        // If there are two sets of coordinates, then append the query parameters to get Capsules within bounded area
        if ($latNE != null && $lngNE != null && $latSW != null && $lngSW != null) {
            $query = $this->appendInRectangleToQuery($latNE, $lngNE, $latSW, $lngSW, $query);
        }

        return $this->find('all', $query);
    }

    /**
     * Gets undiscovered Capsules for the specified User.  If a set of coordinates and a radius are specified, only
     * Capsules that fall into the bounded area will be returned.
     *
     * @param mixed $userId The ID of the User to retrieve the discovered Capsules for.
     * @param null $lat
     * @param null $lng
     * @param null $radius
     * @param array $query
     * @return array|null
     */
    public function getUndiscoveredForUser($userId, $lat = null, $lng = null, $radius = null, $query = array()) {
        // Build query to get undiscovered Capsules for the specified User
        $query = $this->appendUndiscoveredForUserToQuery($userId, $query);
        // Exclude Capsules that have been soft-deleted
        $query = $this->appendSoftDeleteExclusionToQuery($query);
        // Specify the fields that will be returned in the query
        $query = $this->appendCapsuleProjectionToQuery($query);
        // If there are coordinates and a radius, append the query to get Capsules only within the bounded area
        if ($lat != null && $lng != null && $radius != null) {
            $query = $this->appendInRadiusToQuery($lat, $lng, $radius, $query);
        }

        return $this->find('all', $query);
    }

    /**
     * Appends parameters to the query that returns all Capsules within the specified radius around the specified
     * latitude and longitude.
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
    private function appendInRadiusToQuery($lat, $lng, $radius, $query = array()) {
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

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that returns all Capsules within the bounded rectangle
     *
     * @param float $latNE The Northeast latitude
     * @param float $lngNE The Northeast longitude
     * @param float $latSW The Southwest latitude
     * @param float $lngSW The Southwest longitude
     * @param array $query
     * @return array
     */
    private function appendInRectangleToQuery($latNE, $lngNE, $latSW, $lngSW, $query = array()) {
        $append = array(
            'includePoints' => true,
            'conditions' => array(
                "MBRWITHIN(CapsulePoint.point, MULTIPOINT(POINT($latNE, $lngNE), POINT($latSW, $lngSW)))"
            )
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will only retrieve Capsules owned by the User.
     *
     * @param mixed $userId The ID of the User that owns the Capsules
     * @param array $query
     * @return array
     */
    private function appendBelongsToUserToQuery($userId, $query = array()) {
        $append = array(
            'conditions' => array(
                'Capsule.user_id' => $userId
            )
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will result in a join with the Users table so that related User
     * information is retrieved along with the Capsules.
     *
     * @param array $query
     * @return array
     */
    private function appendCapsuleOwnerToQuery($query = array()) {
        $append = array(
            'joins' => array(
                array(
                    'table' => 'users',
                    'alias' => 'User',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.user_id = User.id'
                    )
                )
            ),
            'fields' => array(
                'User.username'
            )
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will exclude any Capsules that have been soft-deleted.
     *
     * @param array $query
     * @return array
     */
    private function appendSoftDeleteExclusionToQuery($query = array()) {
        $append = array(
            'conditions' => array(
                'Capsule.deleted' => false
            )
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will return only Capsules discovered by the specified User.
     *
     * @param mixed $userId ID of the User to return Discoveries for
     * @param array $query
     * @return array
     */
    private function appendDiscoveriesForUserToQuery($userId, $query = array()) {
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

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will return only Capsules that have not been discovered by the specified
     * User.
     *
     * @param mixed $userId ID of the User to return the undiscovered Capsules for
     * @param array $query
     * @return array
     */
    private function appendUndiscoveredForUserToQuery($userId, $query = array()) {
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

        // Merge the queries
        $query = array_merge_recursive($query, $append);

        // Group the results by the Capsule primary key
        // NOTE: Needs to be done after merging queries or else a null entry will be added to the group array
        $query['group'] = array('Capsule.id');

        return $query;
    }

    /**
     * Appends parameters to the query that will return a Capsule's Memoir along with the Capsule.
     *
     * @param array $query
     * @return array
     */
    private function appendMemoirsToQuery($query = array()) {
        // Append
        $append = array(
            'joins' => array(
                array(
                    'table' => 'memoirs',
                    'alias' => 'Memoir',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = Memoir.capsule_id'
                    )
                )
            ),
            'fields' => $this->Memoir->fieldListProjection
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will create a JOIN with the Discovery table to find a Capsule's Discovery
     * statistics.
     *
     * @param array $query
     * @return array
     */
    private function appendDiscoveryStatsToQuery($query = array()) {
        // Add the virtual fields for favorite count and total rating
        $this->virtualFields['discovery_count'] = Capsule::FIELD_DISCOVERY_COUNT;
        $this->virtualFields['favorite_count'] = Capsule::FIELD_FAVORITE_COUNT;
        $this->virtualFields['total_rating'] = Capsule::FIELD_RATING;
        // Build the query options
        $append = array(
            'joins' => array(
                array(
                    'table' => 'discoveries',
                    'alias' => 'DiscoveryStat',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Capsule.id = DiscoveryStat.capsule_id',
                    )
                )
            )
        );

        // Merge the queries
        $query = array_merge_recursive($query, $append);

        // Group the results by the Capsule primary key
        // NOTE: Needs to be done after merging queries or else a null entry will be added to the group array
        $query['group'] = array('Capsule.id');

        return $query;
    }

    /**
     * Appends parameters to the query that will join the Capsule table with the CapsulePoint table.
     *
     * @param array $query
     * @return array
     */
    private function appendCapsulePointsToQuery($query = array()) {
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

    /**
     * Appends the Capsule projection to the query
     *
     * @param array $query The query to append to
     * @return array The updated query
     */
    private function appendCapsuleProjectionToQuery($query = array()) {
        $append = array(
            'fields' => $this->fieldListProjection
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends the Discovery projection to the query
     *
     * @param array $query The query to append to
     * @return array The updated query
     */
    private function appendDiscoveryProjectionToQuery($query = array()) {
        $append = array(
            'fields' => $this->Discovery->fieldListProjection
        );

        return array_merge_recursive($query, $append);
    }

    /**
     * Appends parameters to the query that will add the specified search terms to the WHERE clause as wildcard
     * comparisons with the text based fields of Capsules and Memoirs.
     *
     * @param string $searchString The search string
     * @param array $query
     * @return array
     */
    private function appendSearchToQuery($searchString = "", $query = array()) {
        // Split the string by spaces
        $keywords = explode(" ", $searchString);
        // Surround all keywords with wildcards
        array_walk($keywords, function (&$value, $key) {
            $value = "%$value%";
        });
        // Build the query to append
        $append = array(
            'conditions' => array(
                'OR' => array()
            )
        );
        // Add keywords to WHERE clause
        foreach ($keywords as $keyword) {
            $append['conditions']['OR'][] = array('Capsule.name LIKE' => $keyword);
            $append['conditions']['OR'][] = array('Memoir.title LIKE' => $keyword);
            $append['conditions']['OR'][] = array('Memoir.message LIKE' => $keyword);
        }

        return array_merge_recursive($query, $append);
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
        // Only allow one Memoir to be saved, but future versions may allow many Memoirs to a single Capsule
        $data['Memoir'] = array(
            $data['Memoir'][0]
        );
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
        if (isset($data['Capsule']) && isset($data['Capsule']['lat']) && isset($data['Capsule']['lng'])
            && is_numeric($data['Capsule']['lat']) && is_numeric($data['Capsule']['lng'])
        ) {
            $pointData = $dataSource->query(sprintf("SELECT POINT(%s, %s) as PointData", $data['Capsule']['lat'],
                $data['Capsule']['lng']));
            // Add the spatial data to be saved
            $data['CapsulePoint'] = array(
                'point' => $pointData[0][0]['PointData']
            );
        } else {
            $commit = false;
        }
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

    /**
     * Given an array of Capsule IDs, will calculate statistics related to the Capsules and then save the Capsules with
     * the updated stats
     *
     * @param array $ids The IDs of the Capsules to update
     * @return mixed
     * @throws Exception
     */
    public function updateStats($ids = array()) {
        // To calculate the Discovery stats, need to join with the Discoveries table
        $query = array(
            'includeDiscoveryStats' => true,
            'conditions' => array(
                'Capsule.id' => $ids
            )
        );
        // Specify the fields that will be returned in the query
        $query = $this->appendCapsuleProjectionToQuery($query);
        // Query the Capsules table with a join on the Discoveries table so the stats can be calculated
        $capsules = $this->find('all', $query);
        // Save data array
        $data = array();
        // Iterate through each Capsule and update the stats
        foreach ($capsules as $capsule) {
            // Add this Capsule's updated stats to the save array
            $data[] = array(
                'Capsule' => array(
                    'id' => $capsule['Capsule']['id'],
                    'discovery_count' => $capsule['Capsule']['discovery_count'],
                    'favorite_count' => $capsule['Capsule']['favorite_count'],
                    'total_rating' => $capsule['Capsule']['total_rating'],
                    'modified' => false
                )
            );
        }
        // Save the Capsules with their updated stats
        return $this->saveMany($data, array('fieldList' => $this->fieldListUpdateStats));
    }

    /**
     * Soft-deletes the specified Capsule
     *
     * @param mixed $id The ID of the Capsule to be soft-deleted
     * @return array|bool True on success, otherwise false
     */
    public function softDelete($id) {
        // Set the ID
        $this->id = $id;
        // Save
        return $this->saveField('deleted', true);
    }

}
