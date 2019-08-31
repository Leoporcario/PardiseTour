<?php
require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_Categories extends Zend_Db_Table_Abstract {
	
	protected $_name = 'category';	
	protected $primary = 'IDCategory';
	
	public function showAll()
	{	
		$results = $this->fetchAll();
		return $results;	
	}
	
	public function add($parameters)
	{
		return $this->insert($parameters);	
	}
	
	public function get($id)
	{
		$id = (int)$id;
		$row = $this->fetchRow($this->primary.' = ' . $id);
		if (!$row){
			throw new Exception("No se encontro la categoría $id");
		}
		return $row->toArray();	
	}
	
	public function edit($parameters){
		
		$this->update($parameters, $this->primary.' = '. (int)$parameters[$this->primary]);
		
	}
	
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
}
?>