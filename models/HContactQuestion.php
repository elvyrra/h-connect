<?php

namespace Hawk\Plugins\HConnect;


class HContactQuestion extends Model{
	protected static $tablename = "HContactQuestion";	
	protected static $primaryColumn = "name";


	public function __construct($data = array()){
		parent::__construct($data);
	}

    public static function getByName($name, $userId){
        $example = new DBExample(array(
            'name' => $name,
            'userId' => $userId
        ));
        return self::getByExample($example);
    }
	
    public static function getAllByUserId($userId){
        $example = new DBExample(array(
            'userId' => $userId
        ));
        return self::getListByExample($example);
    }

    public static function getAllInList($userId){
        $example = new DBExample(array(
            'userId' => $userId,
            'displayInList' => 1,
        ));

        return self::getListByExample($example);
    }
}