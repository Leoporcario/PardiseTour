<?php
require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_Parameters extends Zend_Db_Table_Abstract {
	
	protected $_name = 'parameter';	
	protected $primary = 'IDParameter';
	
	public function showAll()
	{	
		$results = $this->fetchAll();
		return $results;	
	}	
		
	public function get($id)
	{
		$id = (int)$id;
		$row = $this->fetchRow($this->primary.' = ' . $id);
		
		if (!$row){
			throw new Exception("No se encontro el usuario $id");
		}
		return $row->toArray();	
	}
	
		
	public function edit($parameters){
            
            unset($parameters['PPassword_rectify']);
            
            $this->update($parameters, $this->primary.' = '. (int)$parameters[$this->primary]);
		
	}	
}
?>