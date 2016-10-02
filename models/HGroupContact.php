<?php

namespace Hawk\Plugins\HConnect;


class HGroupContact extends Model{
	protected static $tablename = "HGroupContact";	
	protected static $primaryColumn = "id";


	public function __construct($data = array()){
		parent::__construct($data);
	}
	

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

	public static function deleteContacts($groupId){
		// start to delete all from groupId
		HGroupContact::deleteByExample(new DBExample(array(
			'groupId' => $groupId,
		)));
	}

	public static function saveContacts($groupId, $contacts){

        foreach($contacts as $contact){
        	HGroupContact::add(array(
				'groupId' => $groupId,
				'contactId' => $contact['id'],
			));
        }
	}
}