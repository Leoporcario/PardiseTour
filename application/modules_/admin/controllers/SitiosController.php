<?php

//Modelos
require_once 'Islas.php';
require_once 'Grupoislas.php';
require_once 'Imagenes.php';
require_once 'Sitios.php';
require_once 'Categoriasitios.php';

//Librerias
require_once 'Common.php';

class Admin_SitiosController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idSitios', 'label' => 'N° Sitios', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'idGrupoIsla', 'label' => 'Grupo de islas', 'order' => false),
        array('field' => 'idIsla', 'label' => 'Ísla', 'order' => false),
        array('field' => 'IDCategoriaSitio', 'label' => 'Ísla', 'order' => false),
        array('field' => 'SInombreEs', 'label' => 'Nombre (Español)', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'GInombreEs', 'label' => 'Grupo de islas', 'list' => true, 'order' => true),
        array('field' => 'ISnombreEs', 'label' => 'Ísla', 'list' => true, 'order' => true),
        array('field' => 'CSnombreEs', 'label' => 'Categoría', 'list' => true, 'order' => true),
        array('field' => 'SInombreEn', 'label' => 'Nombre (Ingles)'),
        array('field' => 'SIimagen', 'label' => 'Imagen'),
    );    
    var $validations = array(
        'idIsla' => 'required',
        'SInombreEs' => 'required',
        'SInombreEn' => 'required',
        'SIimagen' => 'required|valid_image_file',
    );    
    var $actions = array(
        array('type' => 'link', 'label' => 'Volver a Islas', 'icon' => 'backward', 'controller' => 'isla', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar un sitio de interés', 'icon' => 'font', 'controller' => 'sitios', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los sitios', 'icon' => 'list', 'controller' => 'sitios', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $response;
    var $actividadIsla;
    var $islas;
    var $grupoIsla;
    var $imagenes;
    
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
        $this->view->menuCurrent = 'sitios';
        $this->view->currentIcon = 'glyphicon glyphicon-font';
        $this->view->currentBrand = 'Sitios de interés (Íslas)';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Sitios (Ísla) creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Sitios (Ísla) editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Sitios (Ísla) eliminado con éxito!';
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
        $this->actividadIsla = new Model_DBTable_Sitios();
        $this->categoria = new Model_DBTable_Categoriasitios();
        $this->islas = new Model_DBTable_Islas();
        $this->grupoIsla = new Model_DBTable_Grupoislas();
        $this->imagenes = new Model_DBTable_Imagenes();
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
            $results_all = $this->actividadIsla->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('SIimagen');
                
                if($principalImage['SIimagen']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'SIimagen');
                    if($imageName){
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['SIimagen'] = $imageName;
                    }else{
                        //Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                
                $idSitios = $this->actividadIsla->add($_POST);   
                
                //Manejo avisos y redirecciono
                $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
            }catch(Zend_Exception $exception){
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }

        //Datos de BD a la vista
        $this->view->grupoIsla = $this->grupoIsla->showAll();
        $this->view->categoria = $this->categoria->showAll();
        
        //idFormulario para validacion
        $this->view->formId = 'actividadIslaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo sitios de interés (ísla)';
        $this->view->description = 'Complete el formulario para agregar un nuevo sitio';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }    

    public function editAction() {
        $actividadIsla = $this->actividadIsla->get($this->view->parameters['id']);
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id from this isle
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            $_POST['idSitios'] = $actividadIsla['idSitios'];
            
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('SIimagen');
                
                if($principalImage['SIimagen']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'SIimagen');
                    if($imageName){
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $actividadIsla['SIimagen']);
                        $this->_image->deleteFile($this->imageRouteMin . $actividadIsla['SIimagen']);
                        
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['SIimagen'] = $imageName;
                    }else{
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                                
                //Sends full post to database
                $this->actividadIsla->edit($_POST);   
                                
                //Manejo avisos y redirecciono
                $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->editSuccessMessage));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
            }catch(Zend_Exception $exception){
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }

        //Datos de BD a la vista
        $this->view->grupoIsla = $this->grupoIsla->showAll();
        $this->view->categoria = $this->categoria->showAll();

        $this->view->islaActual = $this->islas->get($actividadIsla['idIsla']);
        $this->view->islas = $this->islas->showAll('idGrupoIsla = ' . $this->view->islaActual['idGrupoIsla']);
        
        //idFormulario para validacion
        $this->view->formId = 'actividadIslaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar sitio de interés (ísla)';
        $this->view->description = 'Complete el formulario para editar un sitio de interés';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $actividadIsla;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $isla = $this->actividadIsla->get($id);
                        $this->view->result = $this->actividadIsla->delete_row($id);
                       
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
            $this->view->result = $this->islas->get($id);
        } else {
            $this->_redirect('admin/sitios/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar sitio de interés';
        $this->view->description = 'Seleccione una opción';
    }

}

