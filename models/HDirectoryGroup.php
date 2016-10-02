<?php

namespace Hawk\Plugins\HConnect;


class HDirectoryGroup extends Model{
	protected static $tablename = "HDirectoryGroup";	
	protected static $primaryColumn = "id";


	public function __construct($data = array()){
		parent::__construct($data);
	}
	
}