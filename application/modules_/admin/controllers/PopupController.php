<?php

require_once '../application/models/DbTable/Newsletters.php';

require_once '../application/models/DbTable/Sends.php';

require_once '../application/models/DbTable/SendDetail.php';

require_once '../application/models/DbTable/Suscriptors.php';

require_once '../application/models/DbTable/Parameters.php';

class Admin_PopupController extends Zend_Controller_Action
{

	var $response;
	var $model;        
        var $model_send_detail;
	var $model_newsletter;
        var $model_suscriptor;
        var $model_parameters;

    public function init()
    {
        //USER SESSION
		
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if(!$data){
            $this->_redirect('/admin/login');
        }
        $options = array(
            'layout'	 => 'admin',
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);

		//LAYOUTS
		
                $this->response = $this->getResponse();
		
		$this->view->controller=$this->_getParam('controller');
                $this->view->action=$this->_getParam('action');
               
		//MODELS
		
		$this->model = new Model_DBTable_Sends();	
                
                $this->model_send_detail = new Model_DBTable_SendDetail();
                
                $this->model_newsletter = new Model_DBTable_Newsletters();
                
                $this->model_suscriptor = new Model_DBTable_Suscriptors();
                
                $this->model_parameters = new Model_DBTable_Parameters();
                
		$this->form = new Zend_Form();
		
	}

    /*public function indexAction()
    {

        $this->view->h2="Envíos anteriores";	

        $results = $this->model->showAll();
        
        $this->view->envios = $results; 

    }   */
    public function popupAction(){        
            //parametros del servidor
            $parameters = array(
                'PServer' => 'smtp.server.com',
                'PPort' => 80,
                'PUser' => 'usuario',
                'PPassword' => 'PPassword',
                'PEmail' => 'prensa@fem.org.ar'
            );
            
            $this->view->parameters = $parameters;
        
            $this->_helper->layout->disableLayout();
            
            $this->view->message = 'Envio de newsletter';
            
            $IDEnvio = $this->_getParam('IDEnvio',0);
            
            $send = $this->model->get($IDEnvio);
            
            $this->view->envio = $send;
            
            $newsletter = $this->model_newsletter->get($send['IDNewsletter']);
            
            $this->view->newsletter = $newsletter;
            
            $suscriptors = $this->model_send_detail->getSuscriptor($IDEnvio);
            
            $this->view->suscriptors = $suscriptors;
            
    }

}

