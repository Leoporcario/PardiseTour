<?php

require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_EventoIsla extends Zend_Db_Table_Abstract {

    private static $instance;
    protected $_name = 'adm_evento-isla';
    protected $primary = 'idEventoIsla';
    protected $defultSort = array('EImes DESC', 'EIdia DESC');
    protected $defultOrder = 'ASC';
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Model_DBTable_EventoIsla();
        }
        return self::$instance;
    }

    public function showAll($where = null, $sort = null, $order = null, $limit = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*"));
        $where = ($where == null) ? '1' : $where;
        $sort = ($sort == null) ? $this->defultSort : $sort;
        $order = ($order == null) ? $this->defultOrder : $order;
        $limit = ($limit == null) ? '0' : $order;
        //$select->join('adm_grupo-isla', '`adm_isla`.`idGrupoIsla` = `adm_grupo-isla`.`idGrupoIsla`');
        //$select->joinNatural('adm_isla');
        //$select->joinNatural('adm_grupo-isla');
        $select->where($where);
        $select->order($sort);
        $select->limit($limit);
        $results = $this->fetchAll($select);
        return $results->toArray();
    }

    public function add($parameters) {
        return $this->insert($parameters);
    }

    public function get($id) {
        $id = (int) $id;
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*"));
        $select->where($this->primary . ' = ' . $id);
        $row = $this->fetchRow($select);
        if (!$row) {
            throw new Exception("No se encontro la actividad $id");
        }
        return $row->toArray();
    }

    public function edit($parameters) {

        //$this->update($parameters, $this->_primary.' = '. (int)$parameters["IDCategory"]);

        /* $param = array('CName' => $parameters['CName'], 
          'CPerformance' => $parameters['CPerformance'],
          'CPresentation' => $parameters['CPresentation'],
          'CDescription' => $parameters['CDescription'],
          'CBackground' => $parameters['CBackground']
          );
         */
        $this->update($parameters, $this->primary . ' = ' . (int) $parameters[$this->primary]);
    }

    public function delete_row($id) {
        return $this->delete($this->primary . ' = ' . (int) $id);
    }

}

?>