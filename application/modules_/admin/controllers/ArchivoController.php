<?php

require_once 'Archivos.php';
require_once 'Common.php';

class Admin_ArchivoController extends Zend_Controller_Action {
    
    var $fields = array(
        array('field' => 'idArchivo', 'label' => 'ID ', 'list' => true, 'search' => true, 'width' => 60, 'class' => 'id', 'order' => true),
        array('field' => 'AnombreEs', 'label' => 'Nombre', 'list' => true),
        array('field' => 'AdescripcionEs', 'label' => 'Descripcion', 'list' => true, 'search' => false, 'order' => false),
    );
    
    var $validations = array(
        'AnombreEs' => 'required',
        'AnombreEn' => 'required',
        'AfisicoEs' => 'required|valid_file',
        'AfisicoEn' => 'valid_file',
    );
    
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo archivo', 'icon' => 'floppy-disk', 'controller' => 'archivo',  'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los archivos', 'icon' => 'list', 'controller' => 'archivo',  'action' => 'index'),
    );
    
    var $options = array(
        array('type' => 'link', 'title' => 'Editar', 'icon' => 'edit text-primary', 'controller' => 'noticia', 'action' => 'edit'),
        array('type' => 'link', 'title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'controller' => 'noticia', 'action' => 'delete'),
    );
    var $response;
    var $archivos;
    
    //Helpers
    var $_redirector;
    var $_image;
    
    //Constants
    var $maxlenghtInput = 100;
    var $maxlenghtTextarea = 200;
    var $imageRouteMin;
    var $imageRoute;
    var $fileRoute;

    public function init() {
        //Datos de login y sesiones
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
        $this->view->menuCurrent = 'news';
        $this->view->currentIcon = 'glyphicon glyphicon-floppy-disk';
        $this->view->currentBrand = 'Archivos';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Archivo guardado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Archivo editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Archivo eliminado con éxito!';
        $this->cancelledMessage = '<b>Información:</b> Transacción cancelada.';
        $this->badExtension = '<b>Atención:</b> Extensiones de los archivos subidos invalidas!. <b>Cambios no guardados</b>';

        //Fields, actions, options
        $this->view->fields = $this->fields;
        $this->view->validations = $this->validations;
        $this->view->actions = $this->actions;
        $this->view->options = $this->options;
        $this->view->maxlenghtTextarea = $this->maxlenghtTextarea;
        $this->view->maxlenghtInput = $this->maxlenghtInput;

        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_image = $this->_helper->getHelper('Image');
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;
        $this->messages = $this->_helper->flashMessenger->getMessages();
        
        //Partials
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));

        //Some extra scripts
        
        //Models
        $this->archivos = new Model_DBTable_Archivos();
        $this->form = new Zend_Form();
        
        //Routes
        $this->imageRoute = PUBLIC_PATH . DS . 'images' . DS;
        $this->imageRouteMin = PUBLIC_PATH . DS . 'images' . DS . 'm_';;
        $this->fileRoute = PUBLIC_PATH . DS . 'files' . DS;
    }
    
    public function indexAction(){
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
            $results_all = $this->archivos->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
    
    public function addAction(){
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $esFile = $this->upload->getFileInfo('AfisicoEs');
                
                if($esFile['AfisicoEs']['error'] == 0){
                    $esFileName = $this->_image->processFile($esFile, $this->upload, 'AfisicoEs');
                    if($esFileName){
                        $_POST['AfisicoEs'] = $esFileName;
                        //clear all filters for new files
                        $this->upload->clearFilters();
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }                

                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $enFile = $this->upload->getFileInfo('AfisicoEn');
                
                if($enFile['AfisicoEn']['error'] == 0){
                    $enFileName = $this->_image->processFile($enFile, $this->upload, 'AfisicoEn');
                    if($enFileName){                        
                        $_POST['AfisicoEn'] = $enFileName;
                        //clear all filters for new files
                        $this->upload->clearFilters();
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                
                //Guardo la fecha de carga
                $_POST['AfechaCarga'] = date('Y-m-d');
                $this->archivos->add($_POST);   
                
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
        $this->view->formId = 'archivoForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo archivo';
        $this->view->description = 'Complete el formulario para agregar un nuevo archivo';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }       
    
    public function editAction(){   
        $archivo = $this->archivos->get($this->view->parameters['id']);
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{ 
                //Elimino el archivo si me piden
                if($_POST['eliminarArchivoEn']){
                    $_POST['AfisicoEn'] = '';
                    $this->_image->deleteFile($this->fileRoute . $_POST['eliminarArchivoEn']);
                    unset($_POST['eliminarArchivoEn']);
                }
                
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $esFile = $this->upload->getFileInfo('AfisicoEs');
                
                if($esFile['AfisicoEs']['error'] == 0){
                    //Borro el anterior
                    $this->_image->deleteFile($this->fileRoute . $archivo['AfisicoEs']);
                    
                    $esFileName = $this->_image->processFile($esFile, $this->upload, 'AfisicoEs');
                    if($esFileName){
                        $_POST['AfisicoEs'] = $esFileName;
                        //clear all filters for new files
                        $this->upload->clearFilters();
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }                

                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $enFile = $this->upload->getFileInfo('AfisicoEn');
                if($enFile['AfisicoEn']['error'] == 0){
                    //Borro el anterior
                    $this->_image->deleteFile($this->fileRoute . $archivo['AfisicoEn']);
                    
                    $enFileName = $this->_image->processFile($enFile, $this->upload, 'AfisicoEn');
                    if($enFileName){                        
                        $_POST['AfisicoEn'] = $enFileName;
                        //clear all filters for new files
                        $this->upload->clearFilters();
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                
                $_POST['idArchivo'] = $archivo['idArchivo'];
                $this->archivos->edit($_POST);
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
        
        //idFormulario para validacion
        $this->view->formId = 'archivoForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar archivo';
        $this->view->description = 'Complete el formulario para editar este archivo';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $archivo;
    }
            
    public function orderAction() {
        $type = $this->_getParam('type', 'new');
        if ($this->getRequest()->isPost()) {
            foreach($_POST['IDNoticias'] as $key => $idNoticia){
                $this->archivos->edit(array(
                    'IDNoticia' => $idNoticia,
                    'NOrden' => $key
                ));
            }
        }
        if($type == 'new'){
            $this->view->news = $this->archivos->showAll('IDTipoNoticia <> 3', $this->parameters['sort'], $this->parameters['order']);        
        }else{
            $this->view->news = $this->archivos->showAll('IDTipoNoticia = 3', $this->parameters['sort'], $this->parameters['order']); 
        }
    }  
    
    public function viewAction() {
        $this->view->result = $this->archivos->get($this->parameters["id"]);
    }    
    
   public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->view->result = $this->archivos->get($id);
                        if(!empty($this->view->result['AfisicoEs'])){
                            $this->_image->deleteFile($this->fileRoute . $this->view->result['AfisicoEs']);
                        }
                        if(!empty($this->view->result['AfisicoEn'])){
                            $this->_image->deleteFile($this->fileRoute . $this->view->result['AfisicoEn']);
                        }
                        $this->view->result = $this->archivos->delete_row($id);
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
            $this->view->result = $this->archivos->get($id);
        } else {
            $this->_redirect('admin/archivo/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar archivo';
        $this->view->description = 'Seleccione una opción';
    }

    
}
