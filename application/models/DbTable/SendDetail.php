<?php
require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_SendDetail extends Zend_Db_Table_Abstract {
	
	protected $_name = 'cms_envio_detalle';	
	protected $primary = 'IDEDetalle';
	
	public function showAll(){        
            
                $results = $this->fetchAll();                
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
        
        public function getNewsletter($IDEnvio){
            
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from('envio_detalle',array('newsletter.NLName'));
            $select->where('IDEnvio = '.$IDEnvio);
            $select->joinLeft(array('newsletter'), 'envio_detalle.IDNewsletter = newsletter.IDNewsletter');
            $select->group("newsletter.IDNewsletter");
             
            $results = $this->fetchAll($select);
            
            return $results;
            
        }
        
	 public function getSuscriptor($IDEnvio){
            
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from('envio_detalle',array('suscriptor.SNombre','envio_detalle.*'));
            $select->where('IDEnvio = '.$IDEnvio);
            $select->joinLeft(array('suscriptor'), 'envio_detalle.IDSuscriptor = suscriptor.IDSuscriptor');
            $select->group("suscriptor.IDSuscriptor");
            $select->order("envio_detalle.EDEstado DESC");
            $results = $this->fetchAll($select);            
            return $results;
                
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
	
        public function get_by_suscriptor($IDSuscriptor)
	{
		$IDSuscriptor = (int)$IDSuscriptor;
                
		$row = $this->fetchRow('IDSuscriptor = ' . $IDSuscriptor);
                               
		if (!$row){
			throw new Exception("No se encontro el detalle para el suscriptor $IDSuscriptor");
		}
		return $row->toArray();	
                
	}        
        
        
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
        
        public function delete_row_by_envio($IDEnvio){
		return $this->delete('IDEnvio = ' . (int)$IDEnvio);
	}
	
}
?>