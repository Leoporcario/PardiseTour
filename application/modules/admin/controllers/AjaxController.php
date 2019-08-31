<?php

require_once 'Islas.php';

/* TODO: Bring limits from BD */

class Admin_AjaxController extends Zend_Controller_Action {

    var $form;
    var $response;
    var $islas;

    public function init() {
        $this->form = new Zend_Form();
        $this->_helper->layout->disableLayout();

        $this->islas = new Model_DBTable_Islas();
    }

    public function preDispatch() {
        $this->session = new Zend_Session_Namespace('ajax');
    
        //Define un límite para las imágenes
        $this->session->inputLimit = 9;

        //Imagenes
        if (!$this->session->inputsRendered) {
            $this->session->inputsRendered = 0;
        }

        //Clubes
        if (!$this->session->clubRendered) {
            $this->session->clubRendered = 0;
        }
        
        //Actividad Hotel
        if (!$this->session->actividadHotelRendered) {
            $this->session->actividadHotelRendered = 0;
        }
        
        //Restobar
        if (!$this->session->restobarRendered) {
            $this->session->restobarRendered = 0;
        }
        
        //Evento Hotel
        if (!$this->session->eventoHotelRendered) {
            $this->session->eventoHotelRendered = 0;
        }
        
        //Habitacion
        if (!$this->session->habitacionRendered) {
            $this->session->habitacionRendered = 0;
        }
        
        //Comodidad hotel
        if (!$this->session->comodidadHotelRendered) {
            $this->session->comodidadHotelRendered = 0;
        }
        
        //Comodidad Habitacion
        if (!$this->session->comodidadHabitacionRendered) {
            $this->session->comodidadHabitacionRendered = 0;
        }
    }

    public function inputAction() {
        if ($this->_request->isXmlHttpRequest()) {
            if ($this->session->inputsRendered < $this->session->inputLimit || $this->session->inputLimit == 0) {
                $this->view->enable = true;
                $this->view->inputName = $this->_getParam('inputName', 'images[]');
                $this->view->inputType = $this->_getParam('inputType', 'text');
                $this->view->inputLabel = $this->_getParam('inputLabel', ' ');
                $this->session->inputsRendered++;
            } else {
                $this->view->enable = false;
            }
        }
    }

    //Load islands on targeted select
    public function islasAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $idGrupoIsla = $this->_getParam('idGrupoIsla', '0');
            $idIslaActual = $this->_getParam('idIslaActual', '0');
            if ($idGrupoIsla > 0) {
                $islas = $this->islas->showAll('idGrupoIsla = ' . $idGrupoIsla);
                if (!empty($islas)) {
                    $this->view->islas = $islas;
                    if ($idIslaActual > 0) {
                        $this->view->idIslaActual = $idIslaActual;
                    }
                } else {
                    $this->view->islas = 'No se encontraron islas';
                }
            } else {
                $this->view->islas = 'No se selecciono ningun grupo de islas';
            }
        }
    }

    public function clubAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->clubRendered;
            $this->session->clubRendered++;
        }
    }

    public function actividadhotelAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->actividadHotelRendered;
            $this->session->actividadHotelRendered++;
        }
    }

    public function restobarAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->restobarRendered;
            $this->session->restobarRendered++;
        }
    }

    public function eventohotelAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->eventoHotelRendered;
            $this->session->eventoHotelRendered++;
        }
    }

    public function habitacionAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->habitacionRendered;
            $this->session->habitacionRendered++;
        }
    }
    
    public function comodidadhotelAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->comodidadHotelRendered;
            $this->session->comodidadHotelRendered++;
        }
    }
    
    public function comodidadhabitacionAction() {
        if ($this->_request->isXmlHttpRequest()) {
            $this->view->alreadyRendered = $this->session->comodidadHabitacionRendered;
            $this->session->comodidadHabitacionRendered++;
        }
    }

}
