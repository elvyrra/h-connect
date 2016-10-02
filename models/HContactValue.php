<?php

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the data HContact behavior.
 *
 * @package HConnect
 */
class HContactValue extends Model{

	/**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = "HContactValue";	

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

        'userId' => array(
            'type' => 'INT(11)',
        ),

        'contactId' => array(
            'type' => 'INT(11)',
        ),

        'question' => array(
            'type' => 'VARCHAR(32)'
        ),

        'value' => array(
            'type' => 'TEXT',
        ),
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
        'userId' => array(
            'type' => 'index',
            'fields' => array(
                'userId'
            ),
        ),

        'QValue_ibfk_1' => array(
            'type' => 'foreign',
            'fields' => array(
                'question'
            ),
            'references' => array(
                'model' => 'HContactQuestion',
                'fields' => array(
                    'name'
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
     * Get question value for a specific contact
     *
     * @param string $question  The name of the question
     * @param id  $contactId    The contact id 
     *
     * @return string value
     */
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
	
	/**
     * Delete all question value for a specific contact
     *
     * @param id  $contactId    The contact id 
     */
	public static function deleteAllByContactId($contactId){
		$example = new DBExample(array(
            'contactId' => $contactId
        ));

		self::deleteByExample($example);
	}

	/**
     * Delete all question value for a specific user adn question
     *
     * @param id  $contactId    The contact id 
     */
	public static function deleteAllByQuestionName($question, $userId){
		$example = new DBExample(array(
            'userId' => $userId,
            'question' => $question
        ));

        self::deleteByExample($example);
	}
}