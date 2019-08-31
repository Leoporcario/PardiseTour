<?php
require_once 'Zend/Db/Table/Abstract.php';

require_once 'Suscriptors.php';

require_once 'Categories.php';

class Model_DBTable_SuscriptorCategory extends Zend_Db_Table_Abstract {
	
	protected $_name = 'cms_suscriptor_category_relation';	
        	
	public function showAllBySuscriptor($IDSuscriptor)
	{
		$results = $this->fetchAll("IDSuscriptor='".$IDSuscriptor."'");
		$result_array=$results->toArray();
		
		$result=array();
		foreach($result_array as $key=>$value){
			$result[]=$result_array[$key]["IDCategory"];	
		}
		return $result;
		
	}
        
        public function showAllByCategory($IDCategory)
	{
		$results = $this->fetchAll("IDCategory='".$IDCategory."'");
		$result_array=$results->toArray();
		
		$result=array();
		foreach($result_array as $key=>$value){
			$result[]=$result_array[$key]["IDSuscriptor"];	
		}
		return $result;
		
	}
	
	public function add($parameters)
	{
		return $this->insert($parameters);	
	}
        	
        public function delete_row_by_suscriptor($IDSuscriptor){
		return $this->delete('IDSuscriptor = ' . (int)$IDSuscriptor);
	}
	
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
	
}
?>