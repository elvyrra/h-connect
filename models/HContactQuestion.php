<?php

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the data HContactQuestion behavior.
 *
 * @package HConnect
 */
class HContactQuestion extends Model{

    /**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = "HContactQuestion";	

    /**
     * Primary Column for the table
     *
     * @var string
     */ 
	protected static $primaryColumn = "name";

    /**
     * The table fields
     *
     * @var array
     */
    protected static $fields = array(
        'name' => array(
            'type' => 'VARCHAR(32)',
            'primary' => true, 
        ),

        'userId' => array(
            'type' => 'INT(11)',
        ),

        'type' => array(
            'type' => 'VARCHAR(16)'
        ),

        'parameters' => array(
            'type' => 'TEXT'
        ),

        'displayInList' => array(
            'type' => 'TINYINT(1)',
        ),

        'order' => array(
            'type' => 'INT(11)'
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

        'question' => array(
            'type' => 'unique',
            'fields' => array(
                'name',
                'userId',
            ),
        ),
    );

    /**
     * Constructor
     */
	public function __construct($data = array()){
		parent::__construct($data);
	}

    /**
     * Get question for a specific user
     *
     * @param string $question  The name of the question
     * @param id  $userId    The user id execute the request
     *
     * @return object ContactQuestion
     */
    public static function getByName($name, $userId){
        $example = new DBExample(array(
            'name' => $name,
            'userId' => $userId
        ));
        return self::getByExample($example);
    }
	
    /**
     * Get all questions for a specific user
     *
     * @param id  $userId    The user id execute the request
     *
     * @return array ContactQuestion's list
     */
    public static function getAllByUserId($userId){
        $example = new DBExample(array(
            'userId' => $userId
        ));
        return self::getListByExample($example);
    }

    /**
     * Get all questions to display in list for a specific user
     *
     * @param id  $userId    The user id execute the request
     *
     * @return array ContactQuestion's list
     */
    public static function getAllInList($userId){
        $example = new DBExample(array(
            'userId' => $userId,
            'displayInList' => 1,
        ));

        return self::getListByExample($example);
    }
}