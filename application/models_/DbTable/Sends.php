<?php
require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_Sends extends Zend_Db_Table_Abstract {
	
	protected $_name = 'cms_envio';	
	protected $primary = 'IDEnvio';
	
	public function showAll(){
            
                $select = $this->select();
                $select->setIntegrityCheck(false);
		$select->from('envio', array('envio.*'));
		$select->joinLeft(array('newsletter'), 'envio.IDNewsletter = newsletter.IDNewsletter');
                $select->order("IDEnvio DESC");                
                $results = $this->fetchAll($select);
                
		return $results;
		
	}
        
        public function showAllPublished(){
            
          /*$select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from('comment', array('IDComment', 'CAuthor', 'CStatus'));
            $select->where('CStatus = 0');
            $select->joinLeft(array('new'), 'comment.IDNew = new.IDNew');
            $results = $this->fetchAll($select);
            return $results;*/
                
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from('new',array('*'));
            $select->where('NStatus = 1');
            $select->group("new.IDNew");
            $select->order("new.IDNew DESC");
            $select->joinLeft(array('image'), 'new.IDNew = image.IDNew');
            $results = $this->fetchAll($select);
            
            return $results;
                
        }
        
       
	
        
	public function add($parameters)
	{
                
		$IDNew = $this->insert($parameters);
						
		if($IDNew>0)
		{
			return $IDNew;
			
		}else{
			return -1;	
		}
	}
	
	public function edit($parameters){
		
		$this->update($parameters, $this->primary.' = '. (int)$parameters[$this->primary]);
		
	}
	
	public function get($id)
	{
		$id = (int)$id;
                
		$row = $this->fetchRow($this->primary.' = ' . $id);
               
		if (!$row){
			throw new Exception("No se encontro el vino $id");
		}
		return $row->toArray();	
	}
	
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
	
}
?>