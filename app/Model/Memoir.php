<?php
App::uses('AppModel', 'Model');

/**
 * Memoir Model
 *
 * @property Capsule $Capsule
 */
class Memoir extends AppModel {

    /**
     * actsAs
     *
     * @var array
     */
    public $actsAs = array(
        'HandleUpload' => array(
            'currentModel' => 'Memoir'
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
        )
    );

    /**
     * validate
     *
     * @var array
     */
    public $validate = array(
        'title' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a title.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength', 255),
                'message' => 'The title cannot exceed 255 characters.'
            )
        ),
        'file' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please choose a file.',
                'required' => true
            )
        ),
        'file_location' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a file location.',
                'required' => true
            )
        ),
        'file_public_name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a file name.',
                'required' => true
            )
        ),
        'file_original_name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a file name.',
                'required' => true
            )
        ),
        'file_type' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a file type.',
                'required' => true
            ),
            'maxLength' => array(
                'rule' => array('maxLength', 64),
                'message' => 'The title cannot exceed 64 characters.'
            )
        ),
        'file_size' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
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

    /**
     * List of fields to be returned when querying the Memoir table
     *
     * @var array
     */
    public $fieldListProjection = array(
        'Memoir.id', 'Memoir.title', 'Memoir.message', 'Memoir.file_location',
        'Memoir.file_public_name', 'Memoir.file_type', 'Memoir.file_size', 'Memoir.modified'
    );

    /**
     * Gets the Memoir by ID
     *
     * @param mixed $id The ID of the Memoir to retrieve
     * @return array|null The Memoir data if a matching row is found, otherwise null
     */
    public function getById($id) {
        return $this->find('first', array(
            'conditions' => array(
                'Memoir.id' => $id
            ),
            'files' => $this->fieldListProjection
        ));
    }

}
