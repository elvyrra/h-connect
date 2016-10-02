<?php

namespace Hawk\Plugins\HConnect;

/**
 * This class describes the data HContact behavior.
 *
 * @package HConnect
 */
class HContact extends Model{

    /**
     * The table containing the projects data
     *
     * @var string
     */
	protected static $tablename = "HContact";

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

        'firstName' => array(
            'type' => 'VARCHAR(64)'
        ),

        'lastName' => array(
            'type' => 'VARCHAR(64)'
        ),

        'job' => array(
            'type' => 'VARCHAR(64)'
        ),

        'company' => array(
            'type' => 'VARCHAR(64)'
        ),

        'phoneNumber' => array(
            'type' => 'VARCHAR(16)'
        ),

        'cellNumber' => array(
            'type' => 'VARCHAR(16)'
        ),

        'personalNumber' => array(
            'type' => 'VARCHAR(16)'
        ),

        'email' => array(
            'type' => 'VARCHAR(64)'
        ),

        'address' => array(
            'type' => 'TEXT'
        ),

        'city' => array(
            'type' => 'VARCHAR(64)'
        ),

        'postcode' => array(
            'type' => 'VARCHAR(10)'
        ),

        'country' => array(
            'type' => 'VARCHAR(64)'
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
        'UserId_ibfk' => array(
            'type' => 'foreign',
            'fields' => array(
                'userId'
            ),
            'references' => array(
                'model' => 'User',
                'fields' => array(
                    'id'
                )
            ),
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE'
        ),
    );

    /**
     * Contact's profile
     *
     * @var object
     */
    private $profile;

    /**
     * Constructor
     */
	public function __construct($data = array()){
		parent::__construct($data);

        if(!isset($this->label) && isset($this->id)) {
            if(isset($this->lastName) && isset($this->firstName)){
                $this->label = $this->lastName . ' ' . $this->firstName;

                if(!empty($this->company))
                    $this->label .= ' - ' . $this->company;

                if(!empty($this->cellNumber))
                    $this->label .= ' - GSM: ' . $this->cellNumber;
            }
            else
                $this->label = "-";
        }
	}

	/**
     * Get the user's profile data
     *
     * @param string $prop The property name to get.
     *                     If not set, the function will return an array containing all the profile data
     *
     * @return mixed
     */
    public function getProfileData($prop = ""){
        if(!isset($this->profile)) {
            $sql = 'SELECT Q.name, V.value
    				FROM ' . HContactValue::getTable()  . ' V
                        INNER JOIN ' . HContactQuestion::getTable() . ' Q ON V.question = Q.name
    				WHERE V.contactId = :id';

            $data = App::db()->query(
                $sql,
                array(
                    'id' => $this->id
                ),
                array(
                    'return' => DB::RETURN_ARRAY,
                    'index' => 'name'
                )
            );

            $this->profile = array_map(
                function ($v) {
                    return $v['value'];
                },
                $data
            );
        }
        return $prop ? (isset($this->profile[$prop]) ? $this->profile[$prop] : null) : $this->profile;
    }


    /**
     * Set the user's profile data. This method does not register the data in database, only set in the user properties
     *
     * @param string $prop  The property name to set
     * @param string $value The value to set
     */
    public function setProfileData($prop, $value){
        $this->profile[$prop] = $value;
    }


    /**
     * Save the user's profile in the database
     */
    public function saveProfile(){
        if($this->profile){
            foreach($this->profile as $prop => $value){
                $questionValue = new HContactValue(
                    array(
                    'question' => $prop,
                    'contactId' => $this->id,
                    'value' => $value,
                    'userId' => App::session()->getUser()->id,
                    )
                );
                $questionValue->save();
            }
        }
    }
}