<?php
require_once 'Zend/Db/Table/Abstract.php';

require_once 'News.php';

class Model_DBTable_NewsletterNew extends Zend_Db_Table_Abstract {
	
	protected $_name = 'cms_newsletter_new_relation';	
	
	public function showAll($IDNewsletter, $order = null)
	{
            $select = $this->select();
            $select->from('newsletter_new_relation');
            $select->where('IDNewsletter = ' . (int)$IDNewsletter);
            if($order){
            $select->order($order);
            }
		$results = $this->fetchAll($select);
		$result_array=$results->toArray();
		
		$result=array();
		foreach($result_array as $key=>$value){
			$result[]=$result_array[$key]["IDNoticia"];	
		}
		return $result;
		
	}
	
	public function showAllNamePrincipal($IDNewsletter)
	{
		$model_new = new Model_DBTable_News();
		
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from('newsletter_new_relation', array('NTitle'));
		$select->joinLeft(array('new'), 'newsletter_new_relation.IDNew = new.IDNew');
		
		$results = $this->fetchAll("IDNewsletter='".$IDNewsletter."'");
		
		foreach($results as $new){
			
			if($new['NCategory'] == "Principal"){
				$result_array[] = $model_new->get($new['IDNew']);
				}			
		}
		
		return $result_array;
		
	}
	
	public function showAllNameSecundary($IDNewsletter)
	{
		$model_new = new Model_DBTable_News();
		
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from('newsletter_new_relation', array('NTitle'));
		$select->joinLeft(array('new'), 'newsletter_new_relation.IDNew = new.IDNew');
		
		$results = $this->fetchAll("IDNewsletter='".$IDNewsletter."'");
		
		foreach($results as $new){
			
			if($new['NCategory'] == "Secundary"){
				$result_array[] = $model_new->get($new['IDNew']);
				}			
		}
		
		return $result_array;
		
	}
        
	public function add($parameters)
	{
		return $this->insert($parameters);	
	}
	/*
	public function get($id)
	{
		$id = (int)$id;
		$row = $this->fetchRow($this->primary.' = ' . $id);
		if (!$row){
			throw new Exception("No se encontro la variedad $id");
		}
		return $row->toArray();	
	}
	
	public function edit($parameters){
		
		$this->update($parameters, $this->primary.' = '. (int)$parameters[$this->primary]);
		
	}
	*/
	public function delete_row_by_newsletter($IDNewsletter){
		return $this->delete('IDNewsletter = ' . (int)$IDNewsletter);
	}
	
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
	
}
?>