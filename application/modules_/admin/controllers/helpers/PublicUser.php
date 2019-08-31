<?php
require_once 'Suscriptors.php';

class Zend_Controller_Action_Helper_PublicUser extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    public $session;
    public $suscriptors;
 
    /**
     * Constructor: initialize plugin loader
     * @return void
     */
    public function __construct()
    {
        //Session object
        $this->session = new Zend_Session_Namespace('publicUser');
        $this->pluginLoader = new Zend_Loader_PluginLoader();
        $this->suscriptors = new Model_DBTable_Suscriptors();
    }
    
    public function getSession(){
        $data['user'] = $this->session->user;
        $data['email'] = $this->session->email;
        $data['id'] = $this->session->id;
        $data['log'] = true;
        
        if (!$data || empty($data['email'])) {
            $data = array();
            $data['firstUse'] = $this->getFirstUse();
            $data['log'] = false;
            $return = $data;
        }else{
            $return = $data;
        }
        return $return;
    }
    
    public function setFirstUse($bool){     
        $this->session->firstUse = $bool;
    }
    
    public function getFirstUse(){
        if(isset($this->session->firstUse)){ 
            return $this->session->firstUse;
        }else{
            $this->setFirstUse(1);
            return $this->session->firstUse;
        }
    }
    
    public function setLang($lang){
        $this->session->lang = $lang;
    }
    
    public function getLang(){
        if(!$this->session->lang){
            $this->session->lang = "En";
        }
        return $this->session->lang;
    }
    
    public function login($data = null){
        if($data){
            $where = "SEmail = '" . $data['username'] . "' AND SPassword = '" . $data['password'] . "' AND SEstado = 1";
            $suscriptor = $this->suscriptors->getByEmail($where);
            if($suscriptor[0]['SEmail'] != ''){
                $this->setId($suscriptor[0]['IDSuscriptor']);
                $this->setUser($suscriptor[0]['SNombre']);
                $this->setEmail($suscriptor[0]['SEmail']);
                if($data['rememberMe']){
                    $this->session->setExpirationSeconds(259200);
                }
                return $suscriptor[0];
            }else{
                return false;
            }
        }
    }
    
    //id
    public function setId($id){
        $this->session->id = $id;
    }    
    public function getId(){
        return $this->session->id;
    }
    
    //User
    public function setUser($user){
        $this->session->user = $user;
    }    
    public function getUser(){
        return $this->session->user;
    }
    
    //Email
    public function setEmail($email){
        $this->session->email = $email;
    }    
    public function getEmail(){
        return $this->session->email;
    }
    
    //logout
    public function logout(){
        $this->session->unsetAll();
        $this->setFirstUse(0);
    }
    
    public function direct($mensaje)
    {
        return $this->verificar($mensaje);
    }
}
?>
