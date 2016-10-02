<?php

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the data HDirectoryGroup behavior.
 *
 * @package HConnect
 */
class HDirectoryGroup extends Model{

	/**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = "HDirectoryGroup";	

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

        'name' => array(
            'type' => 'VARCHAR(128)'
        ),

        'description' => array(
            'type' => 'TEXT'
        ),

        'userId' => array(
            'type' => 'INT(11)'
        ),

        'ctime' => array(
            'type' => 'INT(11)'
        ),

        'mtime' => array(
            'type' => 'INT(11)'
        ),
    );

    /**
     * The table constraints
     */
    protected static $constraints = array(
    	'name' => array(
            'type' => 'index',
            'fields' => array(
                'name'
            ),
        ),

        'userId' => array(
            'type' => 'index',
            'fields' => array(
                'userId'
            ),
        ),
    );

    /**
     * Constructor
     */
	public function __construct($data = array()){
		parent::__construct($data);
	}
}