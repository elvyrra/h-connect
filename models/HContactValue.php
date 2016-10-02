<?php

namespace Hawk\Plugins\HConnect;


class HContactValue extends Model{
	protected static $tablename = "HContactValue";	
	protected static $primaryColumn = "id";


	public function __construct($data = array()){
		parent::__construct($data);
	}

	public static function getValueByName($question, $contactId){
		$value = "";
		$example = new DBExample(array(
            'contactId' => $contactId,
            'question' => $question,
        ));

		$cv = self::getByExample($example);

		if($cv)
			$value = $cv->value;

		return $value;
	}
	
	public static function deleteAllByContactId($contactId){
		$example = new DBExample(array(
            'contactId' => $contactId
        ));

		self::deleteByExample($example);
	}

	public static function deleteAllByQuestionName($question, $userId){
		$example = new DBExample(array(
            'userId' => $userId,
            'question' => $question
        ));

        self::deleteByExample($example);
	}
}