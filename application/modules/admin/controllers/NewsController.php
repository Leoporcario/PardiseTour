<?php

require_once 'News.php';
require_once 'TipoNoticia.php';
require_once 'Common.php';

class Admin_NewsController extends Zend_Controller_Action {
    
    var $fields = array(
        array('field' => 'IDNoticia', 'label' => 'ID ', 'list' => true, 'search' => true, 'width' => 60, 'class' => 'id', 'order' => true),
        array('field' => 'IDTipoNoticia', 'label' => 'Tipo noticia', 'list' => false),
        array('field' => 'TNNombre', 'label' => 'Tipo noticia', 'list' => true),
        array('field' => 'NFecha', 'label' => 'Fecha', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'NTituloEs', 'label' => 'Titulo', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'NIntroduccionEs', 'label' => 'Introduccion'),
        array('field' => 'NDesarrolloEs', 'label' => 'Desarrollo'),
        array('field' => 'NImagen', 'label' => 'Imagen')
    );
    
    var $validations = array(
        'IDTipoNoticia' => 'required',
        'NFecha' => 'required',
        'NTituloEs' => 'required',
        'NTituloEn' => 'required',
        'NImagen' => 'valid_image_file'
    );
    
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nueva noticia', 'icon' => 'plus', 'controller' => 'news',  'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las noticias', 'icon' => 'list', 'controller' => 'news',  'action' => 'index'),
        array('type' => 'link', 'label' => 'Ordenar noticias', 'icon' => 'sort-by-attributes', 'controller' => 'news', 'action' => 'order'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nuevo tipo de noticia', 'icon' => 'plus', 'controller' => 'newstype',  'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los tipos de noticias', 'icon' => 'list', 'controller' => 'newstype',  'action' => 'index')
    );
    
    var $options = array(
        array('type' => 'link', 'title' => 'Editar', 'icon' => 'edit text-primary', 'controller' => 'noticia', 'action' => 'edit'),
        array('type' => 'link', 'title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'controller' => 'noticia', 'action' => 'delete'),
    );
    var $response;
    var $news;
    var $newsTypes;
    
    //Helpers
    var $_redirector;
    var $_image;
    
    //Constants
    var $maxlenghtInput = 100;
    var $maxlenghtTextarea = 200;
    var $imageRouteMin;
    var $imageRoute;

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
        $this->view->currentIcon = 'glyphicon glyphicon-comment';
        $this->view->currentBrand = 'Noticias';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Noticia guardada con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Noticia editada con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Noticia eliminada con éxito!';
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
        $this->news = new Model_DBTable_News();
        $this->newsTypes = new Model_DBTable_TipoNoticia();
        $this->form = new Zend_Form();
        
        //Routes
        $this->imageRoute = PUBLIC_PATH . DS . 'images' . DS;
        $this->imageRouteMin = PUBLIC_PATH . DS . 'images' . DS . 'm_';
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
            $results_all = $this->news->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
            
            $_POST['IDTipoNoticia'] = 1;
            $_POST['NOrden'] = 1;
            $_POST['NFecha'] = date('Y-m-d');
            
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $imageFile = $this->upload->getFileInfo('NImagen');
                
                if($imageFile['NImagen']['error'] == 0){
                    $imageName = $this->_image->processImage($imageFile, $this->upload, 'NImagen');
                    if($imageName){
                        $_POST['NImagen'] = $imageName;
                        //clear all filters for new files
                        $this->upload->clearFilters();

                        $this->news->add($_POST);   

                        //Manejo avisos y redirecciono
                        $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
            }catch(Zend_Exception $exception){
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }
        //Datos de BD a la vista
        $this->view->tiposNoticia = $this->newsTypes->showAll();
        
        //idFormulario para validacion
        $this->view->formId = 'newsForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nueva noticia';
        $this->view->description = 'Complete el formulario para agregar un nueva noticia';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }       
    
    public function editAction(){   
        $noticia = $this->news->get($this->view->parameters['id']);
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{ 
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $imageFile = $this->upload->getFileInfo('NImagen');
                
                if($imageFile['NImagen']['error'] == 0){
                    $imageName = $this->_image->processImage($imageFile, $this->upload, 'NImagen');
                    if($imageName){
                        $_POST['NImagen'] = $imageName;
                        //Deleting previous file
                        $this->_image->deleteFile($this->imageRoute . $noticia['NImagen']);
                        $this->_image->deleteFile($this->imageRouteMin . $noticia['NImagen']);

                        //clear all filters for new files
                        $this->upload->clearFilters();
                    }else{
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                if($_POST['eliminarImagen']){
                    $_POST['NImagen'] = '';
                    $this->_image->deleteFile($this->imageRoute . $_POST['eliminarImagen']);
                    $this->_image->deleteFile($this->imageRouteMin . $_POST['eliminarImagen']);
                    unset($_POST['eliminarImagen']);
                }
                
                $_POST['IDNoticia'] = $noticia['IDNoticia'];
                $this->news->edit($_POST);
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
        $this->view->tiposNoticia = $this->newsTypes->showAll();
        
        //idFormulario para validacion
        $this->view->formId = 'newsForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar noticia';
        $this->view->description = 'Complete el formulario para editar esta noticia';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $noticia;
    }
            
    public function orderAction() {
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
        // $type = $this->_getParam('type', 'new');
        if ($this->getRequest()->isPost()) {
            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->news->edit(array(
                    'IDNoticia' => $idIsla,
                    'NOrden' => $key
                ));
            }
        }
        $this->view->islas = $this->news->showAll(null, 'NOrden', 'ASC');   
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar Noticias';
        $this->view->description = 'Seleccione una noticia con el mouse, y use las flechas para ordenarlas como desee';     
    }
    
    public function viewAction() {
        $this->view->result = $this->news->get($this->parameters["id"]);
    }    
    
   public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->view->result = $this->news->get($id);
                        if(!empty($this->view->result['NImagen'])){
                            $this->_image->deleteFile($this->imageRoute . $this->view->result['NImagen']);
                            $this->_image->deleteFile($this->imageRouteMin . $this->view->result['NImagen']);
                        }
                        $this->view->result = $this->news->delete_row($id);
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
            $this->view->result = $this->news->get($id);
        } else {
            $this->_redirect('admin/news/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar noticia';
        $this->view->description = 'Seleccione una opción';
    }

    
}
