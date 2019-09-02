<?php

//Modelos
//require_once 'Islas.php';
//require_once 'Grupoislas.php';
require_once 'Imagenes.php';
require_once 'EventoIsla.php';

//Librerias
require_once 'Common.php';

class Admin_EventoController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idEventoIsla', 'label' => 'N° Evento', 'list' => true, 'width' => 100, 'class' => 'id', 'order' => true),
        //array('field' => 'idGrupoIsla', 'label' => 'Grupo de islas', 'order' => false),
        //array('field' => 'idIsla', 'label' => 'Ísla', 'order' => false),
        array('field' => 'EInombreEs', 'label' => 'Nombre (Español)', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'GInombreEs', 'label' => 'Grupo de islas', 'list' => false, 'order' => true),
        array('field' => 'ISnombreEs', 'label' => 'Ísla', 'list' => false, 'order' => true),
        array('field' => 'EInombreEn', 'label' => 'Nombre (Ingles)'),
        array('field' => 'EIdescripcionEs', 'label' => 'Descripción (Español)'),
        array('field' => 'EIdescripcionEn', 'label' => 'Descripción (Ingles)'),
        array('field' => 'EImes', 'label' => 'Mes del año'),
        array('field' => 'EIdiaEs', 'label' => 'Dia del mes'),
        array('field' => 'EIdiaEn', 'label' => 'Dia del mes'),
        array('field' => 'EIimagen', 'label' => 'Imagen'),
        array('field' => 'esDestacado', 'label' => 'Destacado', 'list' => true),
    );    
    var $validations = array(
        //'idIsla' => 'required',
        'EInombreEs' => 'required',
        'EInombreEn' => 'required',
        'EIdia' => 'required|min,1|max,31',
        'EImes' => 'required|min,1|max,12',
        'EIimagen' => 'valid_image_file',
    );    
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo evento', 'icon' => 'calendar', 'controller' => 'evento', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los eventos', 'icon' => 'list', 'controller' => 'evento', 'action' => 'index'),
        array('type' => 'link', 'label' => 'Ordenar eventos', 'icon' => 'sort-by-attributes', 'controller' => 'evento', 'action' => 'order'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $response;
    //var $islas;
    //var $grupoIsla;
    var $imagenes;
    var $eventoIsla;
    
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
        $this->view->menuCurrent = 'eventoisla';
        $this->view->currentIcon = 'glyphicon glyphicon-calendar';
        $this->view->currentBrand = 'Eventos';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Evento creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Evento editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Evento eliminado con éxito!';
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
        $this->eventoIsla = new Model_DBTable_EventoIsla();
        //$this->islas = new Model_DBTable_Islas();
        //$this->grupoIsla = new Model_DBTable_Grupoislas();
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
            $results_all = $this->eventoIsla->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
            $_POST['NOrden'] = 0;
            //unset($_POST['idGrupoIsla']);
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('EIimagen');
                
                if($principalImage['EIimagen']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'EIimagen');
                    if($imageName){
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['EIimagen'] = $imageName;
                    }else{
                        //Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                
                $idEventoIsla = $this->eventoIsla->add($_POST);   
                
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
        //$this->view->grupoIsla = $this->grupoIsla->showAll();
        
        //idFormulario para validacion
        $this->view->formId = 'eventoIslaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo evento';
        $this->view->description = 'Complete el formulario para agregar un nuevo evento';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }    

    public function editAction() {
        $eventoIsla = $this->eventoIsla->get($this->view->parameters['id']);
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id from this isle
            unset($_POST['submit']);
            //unset($_POST['idGrupoIsla']);
            $_POST['idEventoIsla'] = $eventoIsla['idEventoIsla'];
            
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('EIimagen');
                
                if($principalImage['EIimagen']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'EIimagen');
                    if($imageName){
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $eventoIsla['EIimagen']);
                        $this->_image->deleteFile($this->imageRouteMin . $eventoIsla['EIimagen']);
                        
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['EIimagen'] = $imageName;
                    }else{
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                                
                //Sends full post to database
                $this->eventoIsla->edit($_POST);   
                                
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
        //$this->view->grupoIsla = $this->grupoIsla->showAll();
        //$this->view->islaActual = $this->islas->get($actividadIsla['idIsla']);
        //$this->view->islas = $this->islas->showAll('idGrupoIsla = ' . $this->view->islaActual['idGrupoIsla']);
        
        //idFormulario para validacion
        $this->view->formId = 'eventoIslaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar evento';
        $this->view->description = 'Complete el formulario para editar un evento';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $eventoIsla;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $eventoIsla = $this->eventoIsla->get($id);
                        if($eventoIsla['EIimagen']){
                            $this->_image->deleteFile($this->imageRoute . $eventoIsla['EIimagen']);
                            $this->_image->deleteFile($this->imageRouteMin . $eventoIsla['EIimagen']);                            
                        }
                        $this->view->result = $this->eventoIsla->delete_row($id);
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
            $this->view->result = $this->eventoIsla->get($id);
        } else {
            $this->_redirect('admin/evento/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar evento';
        $this->view->description = 'Seleccione una opción';
    }
    // ORDENAR LOS EVENTOS
    public function orderAction() {
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
        if ($this->getRequest()->isPost()) {
            foreach($_POST['idEventosIsla'] as $key => $idEvento){
                $this->eventoIsla->edit(array(
                    'idEventoIsla' => $idEvento,
                    'NOrden' => $key
                ));
            }
        }
        $this->view->islas = $this->eventoIsla->showAll(null, 'NOrden', 'ASC'); 
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar Eventos';
        $this->view->description = 'Seleccione un evento con el mouse, y use las flechas para ordenarlos como desee';     
    }

}

