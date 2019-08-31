<?php

//Modelos
require_once 'Modules.php';

//Librerias

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
       //Datos de login y sesion
        $options = array(
            'layout' => 'admin',
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);
        
        //Gets sessions and autenticates
        $this->_session = $this->_helper->getHelper('Session');
        $data = $this->_session->getSession();
        if(!$data){
            $this->_redirect('/admin/login');
        }else{      
            $this->view->ULoginname = $data->ULoginname;
            $this->view->UName = $data->UName;
            $this->view->ULastname = $data->ULastname;
            $this->view->IDUser = $data->IDUser;            
        }
        
        //Vars and params
        $this->parameters = $this->_request->getParams();
        $this->view->parameters = $this->parameters;
        $this->view->controller = $this->_getParam('controller');
        $this->view->menuCurrent = 'index';
        $this->view->currentIcon = 'glyphicon glyphicon-home';
        $this->view->currentBrand = 'CapicuaMS';
        $this->view->noNavbar = true;
        
        //Messages (error and success)
        $this->warningMessage = 'Ya existe un suscriptor con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Suscriptor creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Suscriptor editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Suscriptor eliminado con éxito!';
        $this->cancelledMessage = '<b>Información:</b> Transacción cancelada.';

        //Fields, actions, options
        $this->view->fields = $this->fields;
        $this->view->actions = $this->actions;
        $this->view->options = $this->options;

        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;
        
        //Modelos
        $this->modules = new Model_DBTable_Modules();

        //Partials
        
        //Some extra scripts
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . "/css/index.css");
        
    }

    public function indexAction(){
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));
        
        try{
            $this->view->cmsModules = $this->modules->showAll('tipo = 0');
            $this->view->admModules = $this->modules->showAll('tipo = 1');      
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
        
        //Sending params and vars to view
        $this->view->messages = $this->messages;
        if($statusBar){
            $this->view->statusBar = $statusBar;            
        }        
    }
    
    public function menuAction(){
        $this->_helper->layout()->disableLayout();
        try{
            $this->view->cmsModules = $this->modules->showAll('tipo = 0');
            $this->view->admModules = $this->modules->showAll('tipo = 1');
        } catch (Zend_Exception $exc) {
            echo "no se encontraron resultados";
            exit();
        }
    }
}

