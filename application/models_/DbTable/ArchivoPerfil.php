<?php
require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_ArchivoPerfil extends Zend_Db_Table_Abstract {
    private static $instance;
	
	protected $_name = 'cms_archivo_perfil';	

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Model_DBTable_ArchivoPerfil();
        }
        return self::$instance;
    }
	
	public function add($parameters)
	{
		return $this->insert($parameters);	
	}

	public function delete_row_by_archivo($IDArchivo){
		return $this->delete('IDArchivo = ' . (int)$IDArchivo);
	}
	public function delete_row_by_perfil($IDPerfil){
		return $this->delete('IDPerfil = ' . (int)$IDPerfil);
	}

	public function getByArchivo($IDArchivo){
		$results = $this->fetchAll("IDArchivo=$IDArchivo");
		$result_array=$results->toArray();
		
		$result=array();
		foreach($result_array as $key=>$value){
			$result[]=$value["IDPerfil"];	
		}
		return $result;
		
	}
	public function getByPerfil($IDPerfil){
		$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*"));
        $select->where("IDPerfil=$IDPerfil");
        $select->joinLeft('adm_archivo as a','cms_archivo_perfil.IDArchivo=a.IDArchivo');
        $results = $this->fetchAll($select);
		$result=$results->toArray();
		return $result;
	}
	
	public function delete_row($id){
		return $this->delete($this->primary.' = ' . (int)$id);
	}
	
}
?>