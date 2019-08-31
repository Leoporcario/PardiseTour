<?php

//Modelos
require_once 'Grupoislas.php';
require_once 'Islas.php';

//Librerias
require_once 'Common.php';

class Admin_GrupoislaController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idGrupoIsla', 'label' => 'N° Grupo de islas', 'list' => true, 'width' => 160, 'class' => 'id', 'order' => true),
        array('field' => 'GInombreEs', 'label' => 'Nombre (Español)', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'GInombreEn', 'label' => 'Nombre (Ingles)')
    );    
    var $validations = array(
        'GInombreEs' => 'required',
        'GInombreEn' => 'required'
    );    
    var $actions = array(
        array('type' => 'link', 'label' => 'Volver a Íslas', 'icon' => 'backward', 'controller' => 'isla', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva información de buceo', 'icon' => 'info-sign', 'controller' => 'buceo', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las informaciones de buceo', 'icon' => 'list', 'controller' => 'buceo', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $response;
    var $grupoIsla;
    var $islas;
    
    //Helpers
    var $_redirector;
    var $_image;
    var $_session;
    
    //Constants
    var $maxlenghtInput = 100;
    var $maxlenghtTextarea = 200;
    var $imageRouteMin;
    var $imageRoute;
    var $fileRoute;

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
        $this->view->menuCurrent = 'grupoisla';
        $this->view->currentIcon = 'glyphicon glyphicon-th-large';
        $this->view->currentBrand = 'Grupos de Íslas';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Grupo de islas creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Grupo de islas editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Grupo de islas eliminado con éxito!';
        $this->cancelledMessage = '<b>Información:</b> Transacción cancelada.';
        $this->badExtension = '<b>Atención:</b> Extensiones de los archivos subidos invalidas!. <b>Cambios no guardados</b>';

        //Fields, actions, options
        $this->view->fields = $this->fields;
        $this->view->validations = $this->validations;
        $this->view->maxlenghtTextarea = $this->maxlenghtTextarea;
        $this->view->maxlenghtInput = $this->maxlenghtInput;
        $this->view->actions = $this->actions;
        $this->view->options = $this->options;

        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_image = $this->_helper->getHelper('Image');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;

        //Partials
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));
        $this->response->insert('imageInputs', $this->view->render('/imageInputs.phtml'));
        $this->response->insert('imageInputsEdit', $this->view->render('/imageInputsEdit.phtml'));

        //Modelos
        $this->grupoIsla = new Model_DBTable_Grupoislas();
        $this->islas = new Model_DBTable_Islas();
        $this->form = new Zend_Form();

        //Routes
        $this->fileRoute = PUBLIC_PATH . DS . 'files' . DS;
        $this->imageRoute = PUBLIC_PATH . DS . 'images' . DS;
        $this->imageRouteMin = PUBLIC_PATH . DS . 'images' . DS . 'm_';
        
        //Extra scripts
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
    }

    public function indexAction() {
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
            $results_all = $this->grupoIsla->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
    }

    public function addAction() { 
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{
               
                $idIsla = $this->grupoIsla->add($_POST);
                
                //Manejo avisos y redirecciono
                $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
            }catch(Zend_Exception $exception){
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }
        
        //idFormulario para validacion
        $this->view->formId = 'grupoislaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo Grupo de islas';
        $this->view->description = 'Complete el formulario para agregar un nuevo Grupo de islas';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }    

    public function editAction() {
        $grupoIsla = $this->grupoIsla->get($this->view->parameters['id']);
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id from this isle
            unset($_POST['submit']);
            $_POST['idGrupoIsla'] = $grupoIsla['idGrupoIsla'];
                        
            try{                
                //Sends full post to database
                $this->grupoIsla->edit($_POST);   
                               
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
        $this->view->formId = 'grupoislaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar Grupo de islas';
        $this->view->description = 'Complete el formulario para editar un grupo de islas';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $grupoIsla;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->view->result = $this->grupoIsla->delete_row($id);
                    } catch (Zend_Exception $exc) {
                        $this->_helper->flashMessenger->addMessage(array('type' => 'error', 'message' => $this->errorMessage));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
                    }
                    $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->deleteSuccessMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
                } else if ($delete_request == "No") {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->cancelledMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                }
            }
        }
        if ($id > 0) {
            $this->view->result = $this->grupoIsla->get($id);
            if(count($this->islas->showAll('idGrupoIsla = ' . $id)) > 0){
                $this->view->warning = 'Atención: Hay islas pertenecientes a este grupo de islas, si lo elimina esas islas deberan ser reasignadas a otro grupo.';
            }
        } else {
            $this->_redirect('admin/grupoisla/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar Grupo de islas';
        $this->view->description = 'Seleccione una opción';
    }

}

