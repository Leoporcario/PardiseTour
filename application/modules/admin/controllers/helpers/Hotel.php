<?php
require_once 'Hoteles.php';
require_once 'ActividadHotel.php';
require_once 'EventoHotel.php';
require_once 'Restobar.php';
require_once 'Habitacion.php';
require_once 'ComodidadHotel.php';
require_once 'ComodidadHabitacion.php';

class Zend_Controller_Action_Helper_Hotel extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    protected $hotel;
    protected $actividadHotel;
    protected $eventoHotel;
    protected $restobar;
    protected $habitacion;
    protected $comodidadHotel;
    protected $comodidadHabitacion;
 
    /**
     * Constructor: initialize plugin loader * 
     * @return void
     */
    public function __construct()
    {
        $this->hotel = new Model_DBTable_Hoteles();
        $this->actividadHotel = new Model_DBTable_ActividadHotel();
        $this->eventoHotel = new Model_DBTable_EventoHotel();
        $this->restobar = new Model_DBTable_Restobar();
        $this->habitacion = new Model_DBTable_Habitacion();
        $this->comodidadHotel = new Model_DBTable_ComodidadHotel();
        $this->comodidadHabitacion = new Model_DBTable_ComodidadHabitacion();
        
    }
    
    /**
     * addActividad: Recieves a activity's structure array and adds to DB by id from activity-hotel's data
     * $actividades => array of activities info
     * $idHotel => id from Hotel instance
     * @return void
     */
    public function addActividades($actividades = null, $idHotel = 0){
        if(is_array($actividades) && count($actividades) > 0 && $idHotel > 0){
            foreach($actividades as $actividad){
                $actividad['idHotel'] = $idHotel;
                $this->actividadHotel->add($actividad);
            }
        }
    }  
    public function addRestobares($restobares = null, $idHotel = 0){
        if(is_array($restobares) && count($restobares) > 0 && $idHotel > 0){
            foreach($restobares as $restobar){
                $restobar['idHotel'] = $idHotel;
                $this->restobar->add($restobar);
            }
        }
    }  
    public function addEventos($eventos = null, $idHotel = 0){
        if(is_array($eventos) && count($eventos) > 0 && $idHotel > 0){
            foreach($eventos as $evento){
                $evento['idHotel'] = $idHotel;
                foreach ($evento['EHdiaEs'] as $key => $day) {
                    switch ($day) {
                        case 'Domingo':
                            $day='Sunday';
                        break;
                        case 'Lunes':
                            $day='Monday';
                        break;
                        case 'Martes':
                            $day='Tuesday';
                        break;
                        case 'Miércoles':
                            $day='Wednesday';
                        break;
                        case 'Jueves':
                            $day='Thursday';
                        break;
                        case 'Viernes':
                            $day='Friday';
                        break;
                        case 'Sábado':
                            $day='Saturday';
                        break;
                    }
                    $evento['EHdiaEn'][$key]=$day;
                }
                $evento['EHdiaEs']=json_encode($evento['EHdiaEs']);
                $evento['EHdiaEn']=json_encode($evento['EHdiaEn']);
                $this->eventoHotel->add($evento);
            }
        }
    }  
    public function addHabitaciones($habitaciones = null, $idHotel = 0){
        if(is_array($habitaciones) && count($habitaciones) > 0 && $idHotel > 0){
            foreach($habitaciones as $habitacion){
                $habitacion['idHotel'] = $idHotel;
                $this->habitacion->add($habitacion);
            }
        }
    }  
    public function addComodidadesHotel($comodidadesHotel = null, $idHotel = 0){
        if(is_array($comodidadesHotel) && count($comodidadesHotel) > 0 && $idHotel > 0){
            foreach($comodidadesHotel as $comodidadHotel){
                $comodidadHotel['idHotel'] = $idHotel;
                $this->comodidadHotel->add($comodidadHotel);
            }
        }
    }  
    public function addComodidadesHabitacion($comodidadesHabitacion = null, $idHotel = 0){
        if(is_array($comodidadesHabitacion) && count($comodidadesHabitacion) > 0 && $idHotel > 0){
            foreach($comodidadesHabitacion as $comodidadHabitacion){
                $comodidadHabitacion['idHotel'] = $idHotel;
                $this->comodidadHabitacion->add($comodidadHabitacion);
            }
        }
    }      
    
    public function editActividades($actividades = array()){
        if(count($actividades) > 0){
            foreach($actividades as $idActividad => $dataActividad){
                $dataActividad['idActividadHotel'] = $idActividad;
                $this->actividadHotel->edit($dataActividad);
            }
        }
    }      
    public function editEventos($eventos = array()){
        if(count($eventos) > 0){
            foreach($eventos as $idEvento => $dataEvento){
                $dataEvento['idEventoHotel'] = $idEvento;
                foreach ($dataEvento['EHdiaEs'] as $key => $day) {
                    switch ($day){
                        case 'Domingo':
                            $day='Sunday';
                        break;
                        case 'Lunes':
                            $day='Monday';
                        break;
                        case 'Martes':
                            $day='Tuesday';
                        break;
                        case 'Miércoles':
                            $day='Wednesday';
                        break;
                        case 'Jueves':
                            $day='Thursday';
                        break;
                        case 'Viernes':
                            $day='Friday';
                        break;
                        case 'Sábado':
                            $day='Saturday';
                        break;
                    }
                    $dataEvento['EHdiaEn'][$key]=$day;
                }
                $dataEvento['EHdiaEs']=json_encode($dataEvento['EHdiaEs']);
                $dataEvento['EHdiaEn']=json_encode($dataEvento['EHdiaEn']);
                $this->eventoHotel->edit($dataEvento);
            }
        }
    }      
    public function editRestobares($restobares = array()){
        if(count($restobares) > 0){
            foreach($restobares as $idGastronomia => $dataRestobar){
                $dataRestobar['idGastronomia'] = $idGastronomia;
                $this->restobar->edit($dataRestobar);
            }
        }
    }  
    public function editHabitaciones($habitaciones = array()){
        if(count($habitaciones) > 0){
            foreach($habitaciones as $idHabitacion => $dataHabitacion){
                $dataHabitacion['idHabitacion'] = $idHabitacion;
                $this->habitacion->edit($dataHabitacion);
            }
        }
    }     
    public function editComodidadesHotel($comodidadesHotel = array()){
        if(count($comodidadesHotel) > 0){
            foreach($comodidadesHotel as $idComodidadHotel => $dataComodidadHotel){
                $dataComodidadHotel['idComodidadHotel'] = $idComodidadHotel;
                $this->comodidadHotel->edit($dataComodidadHotel);
            }
        }
    }    
    public function editComodidadesHabitacion($comodidadesHabitacion = array()){
        if(count($comodidadesHabitacion) > 0){
            foreach($comodidadesHabitacion as $idComodidadHabitacion => $dataComodidadHabitacion){
                $dataComodidadHabitacion['idComodidadHabitacion'] = $idComodidadHabitacion;
                $this->comodidadHabitacion->edit($dataComodidadHabitacion);
            }
        }
    }    
    
    public function getActividades($idHotel = 0){
        if($idHotel > 0){
            return $this->actividadHotel->showAll('idHotel = ' . $idHotel);
        }
    }
    public function getRestobares($idHotel = 0){
        if($idHotel > 0){
            return $this->restobar->showAll('idHotel = ' . $idHotel);
        }
    }
    public function getEventos($idHotel = 0){
        if($idHotel > 0){
            return $this->eventoHotel->showAll('idHotel = ' . $idHotel);
        }
    }
    public function getHabitaciones($idHotel = 0){
        if($idHotel > 0){
            return $this->habitacion->showAll('idHotel = ' . $idHotel);
        }
    }
    public function getComodidadesHotel($idHotel = 0){
        if($idHotel > 0){
            return $this->comodidadHotel->showAll('idHotel = ' . $idHotel);
        }
    }
    public function getComodidadesHabitacion($idHotel = 0){
        if($idHotel > 0){
            return $this->comodidadHabitacion->showAll('idHotel = ' . $idHotel);
        }
    }
    
    public function deleteActividades($actividades = array()){
        if(count($actividades) > 0){
            foreach($actividades as $idActividadHotel){
                $this->actividadHotel->delete_row($idActividadHotel);
            }
        }
    }
    public function deleteEventos($eventos = array()){
        if(count($eventos) > 0){
            foreach($eventos as $idEventoHotel){
                $this->eventoHotel->delete_row($idEventoHotel);
            }
        }
    }
    public function deleteRestobares($restobares =  array()){
        if(count($restobares) > 0){
            foreach($restobares as $idGastronomia){
                $this->restobar->delete_row($idGastronomia);
            }
        }
    }
    public function deleteHabitaciones($habitaciones = array()){
        if(count($habitaciones) > 0){
            foreach($habitaciones as $idHabitacion){
                $this->habitacion->delete_row($idHabitacion);
            }
        }
    }
    public function deleteComodidadesHabitacion($comodidadesHabitacion = array()){
        if(count($comodidadesHabitacion) > 0){
            foreach($comodidadesHabitacion as $idComodidadHabitacion){
                $this->comodidadHabitacion->delete_row($idComodidadHabitacion);
            }
        }
    }
    public function deleteComodidadesHotel($comodidadesHotel = array()){
        if(count($comodidadesHotel) > 0){
            foreach($comodidadesHotel as $idComodidadHotel){
                $this->comodidadHotel->delete_row($idComodidadHotel);
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
