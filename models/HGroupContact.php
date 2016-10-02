<?php

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the data HGroupContact behavior.
 *
 * @package HConnect
 */
class HGroupContact extends Model{

	/**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = "HGroupContact";	

	/**
     * Primary Column for the table
     *
     * @var string
     */	
	protected static $primaryColumn = "id";

     /**
     * The table fields
     *
     * @var array
     */
    protected static $fields = array(
        'id' => array(
            'type' => 'INT(11)',
            'auto_increment' => true
        ),

        'groupId' => array(
            'type' => 'INT(11)',
        ),

        'contactId' => array(
            'type' => 'INT(11)',
        ),
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
        'groupId' => array(
            'type' => 'index',
            'fields' => array(
                'groupId'
            ),
        ),

        'contactId' => array(
            'type' => 'index',
            'fields' => array(
                'contactId'
            ),
        ),

        'groupContact' => array(
            'type' => 'unique',
            'fields' => array(
            	'groupId',
                'contactId'
            ),
        ),

        'HGroupId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'groupId'
            ),
            'references' => array(
                'model' => 'HDirectoryGroup',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),

 		'HContactId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'contactId'
            ),
            'references' => array(
                'model' => 'HContact',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),
    );

    /**
     * Constructor
     */ 
	public function __construct($data = array()){
		parent::__construct($data);
	}
	
	/**
     * Get all contacts into the group
     *
     * @param int  $groupId    The group id 
     *
     * @return array Contact's list
     */
	public static function getAllContactByGroupId($groupId){

		$list = array();
		$contacts = array();

		// Get all groupContact with selected groupId
		$groupContact = self::getListByExample(new DBExample(array(
			'groupId' => $groupId,
		)));

		// Get each contactId from each groupContact
		foreach ($groupContact as $group) {
			array_push($list, $group->contactId);
		}

		if(!empty($list))
			// Get all contacts link to this groupId
			$contacts = HContact::getListByExample(
				new DBExample(
					array(
						'id' => array(
							'$in' => $list
						)	
					) 
				)
			);

		return $contacts;
	}

	/**
     * Delete all contacts into the group
     *
     * @param int  $groupId    The group id 
     */
	public static function deleteContacts($groupId){
		// start to delete all from groupId
		HGroupContact::deleteByExample(new DBExample(array(
			'groupId' => $groupId,
		)));
	}

	/**
     * Save all contacts into the group
     *
     * @param int  $groupId    The group id 
     *
     * @param array  $contacts  Contact's list to save
     */
	public static function saveContacts($groupId, $contacts){

        foreach($contacts as $contact){
        	HGroupContact::add(array(
				'groupId' => $groupId,
				'contactId' => $contact['id'],
			));
        }
	}
}