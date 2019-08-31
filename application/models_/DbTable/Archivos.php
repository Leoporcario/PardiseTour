<?php

require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_Archivos extends Zend_Db_Table_Abstract {

    private static $instance;
    protected $_name = 'adm_archivo';
    protected $primary = 'idArchivo';
    protected $defultSort = 'AnombreEs';
    protected $defultOrder = 'ASC';
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Model_DBTable_Archivos();
        }
        return self::$instance;
    }
    
    public static function getPermisos($suscriptor){
        $permisos = array();
        if($suscriptor['Sbase'] == 1){
            $permisos['Abase'] = 1;
        }
        if($suscriptor['Sbajo'] == 1){
            $permisos['Abajo'] = 1;
        }
        if($suscriptor['Smedio'] == 1){
            $permisos['Amedio'] = 1;
        }
        if($suscriptor['Salto'] == 1){
            $permisos['Aalto'] = 1;
        }
        return $permisos;
    }
    
    public static function getFileClearences($file){
        $clearences = array();
        if($file['Abase'] == 1){
            $clearences[] = array(
                'nombreEs' => 'Base',
                'nombreEn' => 'Base',
                'status' => 'info'
            );
        }
        if($file['Abajo'] == 1){
            $clearences[] = array(
                'nombreEs' => 'Bajo',
                'nombreEn' => 'Low',
                'status' => 'success'
            );
        }
        if($file['Amedio'] == 1){
            $clearences[] = array(
                'nombreEs' => 'Medio',
                'nombreEn' => 'Medium',
                'status' => 'warning'
            );
        }
        if($file['Aalto'] == 1){
            $clearences[] = array(
                'nombreEs' => 'Alto',
                'nombreEn' => 'High',
                'status' => 'danger'
            );
        }     
        return $clearences;
    }
    
    public static function getFilesByClearence($clearences, $sort = null, $order = null){
        $model = self::getInstance();
        $files = $model->showAll(null, 'AfechaCarga', 'DESC', null);
        //Formateo array de files
        $archivos = array();
        foreach($files as $keyFile => $file){
            foreach($clearences as $codigoPermiso => $permiso){
                if($file[$codigoPermiso] == $permiso){
                    $archivos[$file['idArchivo']] = $file;
                }
            }
        }
        return $archivos;
    }

    public function showAll($where = null, $sort = null, $order = null, $limit = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*"));
        $where = ($where == null) ? '1' : $where;
        $sort = ($sort == null) ? $this->defultSort : $sort;
        $order = ($order == null) ? $this->defultOrder : $order;
        $limit = ($limit == null) ? '0' : $order;
        $select->where($where);
        $select->order($sort . ' ' . $order);
        $select->limit($limit);
        $results = $this->fetchAll($select);
        return $results->toArray();
    }

    public function add($parameters) {
        return $this->insert($parameters);
    }

    public function get($id) {
        $id = (int) $id;
        $row = $this->fetchRow($this->primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se encontro la categoría $id");
        }
        return $row->toArray();
    }

    public function edit($parameters) {
        $this->update($parameters, $this->primary . ' = ' . (int) $parameters[$this->primary]);
    }

    public function delete_row($id) {
        return $this->delete($this->primary . ' = ' . (int) $id);
    }

}

?>