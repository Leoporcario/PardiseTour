<?php

//Modelos
require_once 'Perfiles.php';
require_once 'Enlaces.php';
//Librerias
require_once 'PHPExcel/PHPExcel.php';
require_once 'Common.php';
require_once 'ArchivoPerfil.php';

class Admin_PerfilesController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'IDPerfil', 'label' => 'N° Perfil', 'list' => true, 'width' => 200, 'class' => 'id', 'order' => true),
        array('field' => 'PNombre', 'label' => 'Nombre ', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo perfil', 'icon' => 'tags', 'controller' => 'perfiles', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los perfiles', 'icon' => 'list', 'controller' => 'perfiles', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );    
    
    var $response;
    var $Perfiles;
    var $enlaces;


    public function init() {
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
        $this->view->menuCurrent = 'perfiles';
        $this->view->currentIcon = 'glyphicon glyphicon-lock';
        $this->view->currentBrand = 'Perfiles';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Categoría (Hotel) creada con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Categoría (Hotel) editada con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Categoría (Hotel) eliminada con éxito!';
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

        //Partials
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));

        //Modelos
        $this->Perfiles = new Model_DBTable_Perfiles();
        $this->form = new Zend_Form();

        //Extra scripts
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
    }

    public function indexAction() {
        try {
            
        
        if($this->_getParam('search', '') != ''){
            $statusBar = 'info';
            $this->view->searched = $this->_getParam('search', '');
            $where = create_where($this->_getParam('search', ''), $this->fields);
        }
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if ($this->form->isValid($post)) {
                $statusBar = 'info';
                $this->view->searched = $this->getRequest()->getPost('search', '');
                $where = create_where($this->getRequest()->getPost('search', ''), $this->fields);
            }
        }
        try {
            $results_all = $this->Perfiles->showAll($where, $this->parameters['sort'], $this->parameters['order']);
            $paginator = Zend_Paginator::factory($results_all);
            $paginator->setItemCountPerPage(COUNTPERPAGE)
                    ->setCurrentPageNumber($this->_getParam('page', 1))
                    ->setPageRange(PAGERANGE);

            $this->view->results = $paginator;
            $this->view->enableSearch = true;
        } catch (Zend_Exception $exc) {
            echo $exc->getMessage();
            exit();
        }

        //Sending params and vars to view
        $this->view->messages = $this->messages;
        if($statusBar){
            $this->view->statusBar = $statusBar;            
        }
        } catch (Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            exit();
        }
    }

    public function addAction() {    
        //Accion con POST
        if ($this->getRequest()->isPost()) {
            //Elimino submit y otros no utiles
            unset($_POST['submit']);
            //Llamo al modelo y guardo los datos
            try {                
                $id = $this->Perfiles->add($_POST);                
            } catch (Zend_Exception $exc) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
            //Manejo avisos y redirecciono
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }

        //idFormulario para validacion
        $this->view->formId = 'PerfilesForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo perfil';
        $this->view->description = 'Complete el formulario para agregar un perfil de usuario';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }   

    public function editAction() {
        $Perfiles = $this->Perfiles->get($this->view->parameters['id']);
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{                 
                $_POST['IDPerfil'] = $Perfiles['IDPerfil'];
                $this->Perfiles->edit($_POST);
                //Manejo avisos y redirecciono
                $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->editSuccessMessage));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));                              
            }catch(Zend_Exception $exception){
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }        
        //idFormulario para validacion
        $this->view->formId = 'PerfilesForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar Perfil';
        $this->view->description = 'Complete el formulario para editar la información de este perfil de usuario';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $Perfiles;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                   
                        $this->view->result = $this->Perfiles->delete_row($id);
                    Model_DBTable_ArchivoPerfil::getInstance()->delete_row_by_perfil($id);
                    $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->deleteSuccessMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
                } else if ($delete_request == "No") {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->cancelledMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                }
            }
        }
        if ($id > 0) {
            $this->view->result = $this->Perfiles->get($id);
        } else {
            $this->_redirect('admin/perfiles/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar perfil';
        $this->view->description = 'Seleccione una opción - Se eliminará este perfil';
    }

}

