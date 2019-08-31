<?php
//require_once '../application/modules/visor/models/DbTable/Alert.php';

class Zend_Controller_Action_Helper_Session extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    
 
    /**
     * Constructor: initialize plugin loader * 
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    public function getSession(){        
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if (!$data || empty($data->ULoginname)) {
            $return = false;
        }else{
            $return = $data;
        }
        return $return;
    }
    
    public function clearInputRendered(){        
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->inputsRendered = 0;
    }
    
    public function setInputRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->inputsRendered = $rendered;
    }
    
    public function getInputLimit(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->inputLimit;
    }
    
    public function setClubRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->clubRendered = $rendered;
    }
    
    public function getClubRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->clubRendered;
    }    
    
    public function setActividadHotelRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->actividadHotelRendered = $rendered;
    }
    
    public function getActividadHotelRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->actividadHotelRendered;
    }
    
    public function setRestobarRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->restobarRendered = $rendered;
    }
    
    public function getRestobarRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->restobarRendered;
    }
    
    public function setEventoHotelRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->eventoHotelRendered = $rendered;
    }
    
    public function getEventoHotelRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->eventoHotelRendered;
    }
    
    //Habitacion
    public function setHabitacionRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->habitacionRendered = $rendered;
    }    
    public function getHabitacionRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->habitacionRendered;
    }
    
    //Comodidad Hotel
    public function setComodidadHotelRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->comodidadHotelRendered = $rendered;
    }    
    public function getComodidadHotelRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->comodidadHotelRendered;
    }
    
    //Comodidad Habitacion
    public function setComodidadHabitacionRendered($rendered){
        $this->session = new Zend_Session_Namespace('ajax');
        $this->session->comodidadHabitacionRendered = $rendered;
    }    
    public function getComodidadHabitacionRendered(){
        $this->session = new Zend_Session_Namespace('ajax');
        return $this->session->comodidadHabitacionRendered;
    }
    
    public function direct($mensaje)
    {
        return $this->verificar($mensaje);
    }
}
?>
