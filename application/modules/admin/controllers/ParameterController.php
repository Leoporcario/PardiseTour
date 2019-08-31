<?php
//require_once 'Zend/Controller/Action.php';

require_once '../application/models/DbTable/Parameters.php';

class Admin_ParameterController extends Zend_Controller_Action
{

	var $response;
	var $model;
	
    public function init()
    {
         $options = array(
            'layout'	 => 'admin',
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);
		//USER SESSION
        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if(!$data){
            $this->_redirect('/admin/login');
        }
        $this->view->ULoginname = $data->ULoginname;
        $this->view->UName = $data->UName;
        $this->view->ULastname = $data->ULastname;

        //LAYOUTS
        $this->response = $this->getResponse();
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('titleMenu', 'Administrador de Usuarios');
        $this->view->menuCurrent='parameter';
        $this->view->controller=$this->_getParam('controller');
        $this->view->action=$this->_getParam('action');
        $this->response->insert('menu', $this->view->render('/menu.phtml'));
        $this->response->insert('subMenu', $this->view->render('/parameter/menu.phtml'));
        //MODELS
        $this->model = new Model_DBTable_Parameters();				
        $this->form = new Zend_Form();		
    }

    public function indexAction()
    {			
        $this->view->h2 = "Datos del Servidor";
        
        $id = 1;
        
        $params = $this->model->get($id);       
       
        $this->view->parameters = $params;
        
    }	
		
    public function editAction()
    { 
            $id = 1;


            if($id>0){
                
                if($this->getRequest()->isPost())
                {
                    if($this->form->isValid($this->getRequest()->getPost()))
                    {

                        $this->model->edit($_POST);
                        $this->view->message='Parametros modificados exitosamente.';

                    }

                }

                $this->view->parameters = $this->model->get(1);		

                $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");

                $this->view->h2 = "Editar Servidor de correo";				

            }else{
                    $this->_redirect('admin/parameter/');
            }
    }
	
	public function viewAction()
        {
            $id=$this->_getParam('id',0);
            if($id>0){

                    $user = $this->model->get($id);			

                    $this->view->result = $user;

                   $this->view->h2 = "Usuario > #".$id; 

            }else{
                    $this->_redirect('admin/parameter/');
            }
        }
       
       
       }

?>