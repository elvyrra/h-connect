<?php

namespace Hawk\Plugins\HConnect;


class HContact extends Model{
	protected static $tablename = "HContact";	
	protected static $primaryColumn = "id";

	private $profile;

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