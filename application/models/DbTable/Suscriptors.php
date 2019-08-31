<?php

require_once 'Zend/Db/Table/Abstract.php';

class Model_DBTable_Suscriptors extends Zend_Db_Table_Abstract {

    private static $instance;
    protected $_name = 'cms_suscriptor';
    protected $primary = 'IDSuscriptor';
    protected $defultSort = 'SEstado';
    protected $defultOrder = 'ASC';
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Model_DBTable_Suscriptors();
        }
        return self::$instance;
    }

    public function showAll($where = null, $sort = null, $order = null, $limit = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*","(CASE " . $this->_name . ".SEstado when 1 then 'SI' when 0 then 'NO' end ) as SEstado"));
        $where = ($where == null) ? '1' : $where;
        $sort = ($sort == null) ? $this->defultSort : $sort;
        $order = ($order == null) ? $this->defultOrder : $order;
        $limit = ($limit == null) ? '0' : $order;
        $select->where($where);
        $select->joinLeft('adm_perfiles as p','cms_suscriptor.SIDPerfil=p.IDPerfil');
        $select->order($sort . ' ' . $order);
        $select->limit($limit);
        $results = $this->fetchAll($select);
        return $results->toArray();
    }

    public function getByEmail($where = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array($this->_name), array("*"));
        $where = ($where == null) ? '1' : $where;
        $select->where($where);
        $select->limit(1);
        $results = $this->fetchAll($select);
        return $results->toArray();
    }

    public function showAllSlider($where = null, $limit = null) {
        $select = $this->select();
        $select->from('new');
        if ($where != '') {
            $select->where($where . ' AND NTitleE <> "" AND NBodyE <> ""');
        } else {
            $select->where('NTitleE <> "" AND NBodyE <> ""');
        }
        $select->order('NDate DESC');
        if ($limit) {
            $select->limit($limit);
        }
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