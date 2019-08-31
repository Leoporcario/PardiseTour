<?php
require_once 'Zend/Db/Table/Abstract.php';
require_once 'NewsletterNew.php';

class Model_DBTable_Newsletters extends Zend_Db_Table_Abstract {
	
	protected $_name = 'cms_newsletter';	
	protected $primary = 'IDNewsletter';
        protected $defultSort = 'IDNewsletter';
        protected $defultOrder = 'ASC';
	
        public function showAll($where = null, $sort = null, $order = null) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(array($this->_name), array("*"));
            $where = ($where == null) ? '1' : $where;
            $sort = ($sort == null) ? $this->defultSort : $sort;
            $order = ($order == null) ? $this->defultOrder : $order;
            $select->where($where);
            $select->order($sort . ' ' . $order);
            $results = $this->fetchAll($select);
            return $results->toArray();
        }
	
	public function add($parameters)
	{	
				
		unset($parameters["IDNewPrincipal"]);
		
		unset($parameters["IDNewSecundary"]);
		
		unset($parameters["IDNewVisit"]);
		
              /*  print_r($parameters);
                exit();*/
                
		$IDNewsletter = $this->insert($parameters);
		
		
		if($IDNewsletter>0)
		{
			return $IDNewsletter;
			
		}else{
			return -1;	
		}
	}	
        
        public function add_html($parameters){
            
            $this->update($parameters, $this->primary.' = '. (int)$parameters[$this->primary]);
            
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
	
        public function showLastPublished(){
            $select = $this->select();
            $select->from('newsletter',array('IDNewsletter','IDType'));
            $select->order("IDNewsletter DESC");
            $select->limit(1);
         
            $results = $this->fetchAll($select);
            
		return $results->toArray();
                
        }
        
        public function get_by_type($IDType){
            
            $IDType = (int)$IDType;
            $row = $this->fetchAll('IDType = ' . $IDType);
            if (!$row){
                throw new Exception("No se encontro newsletter para la plantilla $IDType");
            }
            return $row->toArray();	
            
        }
        
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
	
}
?>