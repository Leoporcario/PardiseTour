<?php
require_once 'Clubes.php';

class Zend_Controller_Action_Helper_Club extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    protected $clubes;
 
    /**
     * Constructor: initialize plugin loader * 
     * @return void
     */
    public function __construct()
    {
        $this->clubes = new Model_DBTable_Clubes();
        
    }
    
    /**
     * addClubs: Recieves a club's structure array and adds to DB by id from Buceo-isla data
     * $clubes => array of club's info
     * $idBuceoIsla => id from Buceo-isla instance
     * @return void
     */
    public function addClubs($clubes = null, $idBuceoIsla = 0){
        if(is_array($clubes) && count($clubes) > 0 && $idBuceoIsla > 0){
            foreach($clubes as $club){
                $club['idBuceoIsla'] = $idBuceoIsla;
                $this->clubes->add($club);
            }
        }
    }  
    
    /**
     * getClubs: Recieves an buceo-isla's id and gets all clubs from that id
     * $idBuceoIsla => id from Buceo-isla instance
     * @return array of clubs
     */
    public function getClubs($idBuceoIsla = 0){
        if($idBuceoIsla > 0){
            $return = $this->clubes->showAll('idBuceoIsla = ' . $idBuceoIsla);
        }else{
            $return = array();
        }
        return $return;
    }  
    
    /**
     * deleteClubs: Recieves an buceo-isla's id and delete all from DB
     * $clubs => array of idClubBuceo
     * @return void
     */
    public function deleteClubs($clubs = array()){
        if(count($clubs) > 0){
            foreach($clubs as $idClubBuceo){
                $this->clubes->delete_row($idClubBuceo);
            }
        }
    }  
    
    /**
     * editClubs: Recieves an buceo-isla's id and edit the data from array
     * $clubs => array of idClubBuceo
     * @return void
     */
    public function editClubs($clubs = array()){
        if(count($clubs) > 0){
            foreach($clubs as $idClubBuceo => $dataClub){
                $dataClub['idClubBuceo'] = $idClubBuceo;
                $this->clubes->edit($dataClub);
            }
        }
    }  
    
    /**
     * Strategy pattern: call helper as broker method
     * 
     * @param  int $month 
     * @param  int $year 
     * @return int
     */
    public function direct($mensaje)
    {
        return $this->verificar($mensaje);
    }
}
?>
