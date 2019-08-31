<?php

//Modelos
require_once 'Islas.php';
require_once 'Grupoislas.php';
require_once 'Buceo.php';
require_once 'Imagenes.php';
require_once 'Clubes.php';

//Librerias
require_once 'Common.php';

class Admin_BuceoController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idIsla', 'label' => 'Isla', 'list' => false),
        array('field' => 'idBuceoIsla', 'label' => 'N° Info Buceo', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'GInombreEs', 'label' => 'Grupo de islas', 'list' => true),
        array('field' => 'ISnombreEs', 'label' => 'Isla', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'BIdescripcionEs', 'label' => 'Descripción (Español)'),
        array('field' => 'BIdescripcionEn', 'label' => 'Descripción (Ingles)'),
    );    
    var $validations = array(
        'idIsla' => 'required',
        'BIdescripcionEs' => 'required',
        'BIdescripcionEn' => 'required',
        'images[]' => 'valid_image_file'
    );    
    var $actions = array(
        array('type' => 'link', 'label' => 'Volver a Islas', 'icon' => 'backward', 'controller' => 'isla', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva información de buceo', 'icon' => 'info-sign', 'controller' => 'buceo', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las informaciones de buceo', 'icon' => 'list', 'controller' => 'buceo', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $response;
    var $buceo;
    var $islas;
    var $grupoIsla;
    var $imagenes;
    var $clubes;
    
    //Helpers
    var $_redirector;
    var $_image;
    var $_session;
    var $_club;
    
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
        $this->view->menuCurrent = 'buceo';
        $this->view->currentIcon = 'glyphicon glyphicon-info-sign';
        $this->view->currentBrand = 'Buceo';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Información de buceo creada con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Información de buceo editada con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Información de buceo eliminada con éxito!';
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
        $this->_club = $this->_helper->getHelper('Club');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;

        //Partials
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('paginationControl', $this->view->render('/paginationControl.phtml'));
        $this->response->insert('imageInputs', $this->view->render('/imageInputs.phtml'));
        $this->response->insert('imageInputsEdit', $this->view->render('/imageInputsEdit.phtml'));

        //Modelos
        $this->buceo = new Model_DBTable_Buceo();
        $this->islas = new Model_DBTable_Islas();
        $this->grupoIsla = new Model_DBTable_Grupoislas();
        $this->imagenes = new Model_DBTable_Imagenes();
        $this->clubes = new Model_DBTable_Clubes();
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
            $results_all = $this->buceo->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax
        $this->_session->setInputRendered(3);
        //Setting how many clubs already uploaded
        $this->_session->setClubRendered(1);
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            
            //Set variable for clubs
            if($_POST['club']){
                $clubes = $_POST['club'];
                unset($_POST['club']);
            }
            try{          
                $idBuceoIsla = $this->buceo->add($_POST);
                //add clubes to this island 
                if($clubes){
                    $this->_club->addClubs($clubes, $idBuceoIsla);      
                }
                $this->upload = new Zend_File_Transfer_Adapter_Http();       
                
                $images = $this->upload->getFileInfo('images');
                
                if(count($images) > 0){
                    foreach($images as $keyImage => $image){
                        if($images[$keyImage]['error'] == 0){
                            $fileName = $this->_image->processImage($images, $this->upload, $keyImage);
                            if($fileName){
                                //Add filename to list of uploaded files if successful
                                $uploadedFiles[] = $this->imageRoute . $fileName;
                                //Add image to image table
                                $this->imagenes->add(array(
                                    'Inombre' => $fileName,
                                    'idBuceoIsla' => $idBuceoIsla
                                ));
                            }else{
                                //If extensions are bad => Delete all uploaded files 
                                $this->_image->deleteFiles($uploadedFiles);
                                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                                $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                            }
                        }
                    }
                }
                
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
        
        //idFormulario para validacion
        $this->view->formId = 'buceoForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nueva Información de buceo';
        $this->view->description = 'Complete el formulario para agregar información de buceo para una isla en particular';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }    

    public function editAction() {
        $buceo = $this->buceo->get($this->view->parameters['id']);
        $clubes = $this->_club->getClubs($buceo['idBuceoIsla']);
        $imagenes = $this->imagenes->showAll('idBuceoIsla = ' . $buceo['idBuceoIsla']);
        
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        
        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax (+3 inputs on partial imageInputsEdit)
        $this->_session->setInputRendered(count($imagenes) + 3);
        $this->view->inputLimit = $this->_session->getInputLimit();
        //Setting how many clubs already uploaded
        $this->_session->setClubRendered(count($clubes));
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            $_POST['idBuceoIsla'] = $buceo['idBuceoIsla'];
            //Set variable for images to delete
            if($_POST['eliminarImagenes']){
                $eliminarImagenes = $_POST['eliminarImagenes'];
                unset($_POST['eliminarImagenes']);
            }
            //Set variable for clubs to delete
            if($_POST['eliminarClubes']){
                $eliminarClubes = $_POST['eliminarClubes'];
                unset($_POST['eliminarClubes']);
            }          
            //Set variable for new clubs
            if($_POST['club']){
                $clubesNew = $_POST['club'];
                unset($_POST['club']);
            }       
            //Set variable for old clubs
            if($_POST['clubOld']){
                $clubesOld = $_POST['clubOld'];
                unset($_POST['clubOld']);
            }
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();                
                //Delete all flagged images if any flagged
                if($eliminarImagenes){
                    $this->_image->deleteFromTable($eliminarImagenes);
                }              
                //Editing clubs
                if($clubesOld){
                    $this->_club->editClubs($clubesOld);
                }                
                //Add new clubs            
                if($clubesNew){
                    $this->_club->addClubs($clubesNew, $buceo['idBuceoIsla']);      
                }         
                //Deleting unwanted clubs                
                if($eliminarClubes){
                    $this->_club->deleteClubs($eliminarClubes);
                }    
                //Sends full post to database
                $this->buceo->edit($_POST);   
                if($_FILES['images']){
                    $images = $this->upload->getFileInfo('images');
                    if(count($images) > 0){
                        foreach($images as $keyImage => $image){
                            if($images[$keyImage]['error'] == 0){
                                $fileName = $this->_image->processImage($images, $this->upload, $keyImage);
                                if($fileName){
                                    //Add filename to list of uploaded files if successful
                                    $uploadedFiles[] = $this->imageRoute . $fileName;
                                    $uploadedFiles[] = $this->imageRouteMin . $fileName;
                                    //Add image to image table
                                    $this->imagenes->add(array(
                                        'Inombre' => $fileName,
                                        'idBuceoIsla' => $_POST['idBuceoIsla']
                                    ));
                                }else{
                                    //Delete all uploaded files 
                                    $this->_image->deleteFiles($uploadedFiles);
                                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                                }
                            }
                        }
                    }
                }
                
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
        $this->view->islaActual = $this->islas->get($buceo['idIsla']);
        $this->view->islas = $this->islas->showAll('idGrupoIsla = ' . $this->view->islaActual['idGrupoIsla']);
        
        //idFormulario para validacion
        $this->view->formId = 'buceoForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar Información de buceo';
        $this->view->description = 'Complete el formulario para editar la información de buceo de una isla en particular';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $buceo;
        $this->view->images = $imagenes;
        $this->view->clubes = $clubes;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $buceo = $this->buceo->get($id);
                        $this->view->result = $this->buceo->delete_row($id);
                        $images = $this->imagenes->showAll('idBuceoIsla = ' . $id);
                        $clubes = $this->clubes->showAll('idBuceoIsla = ' . $id);
                        if(count($clubes) > 0){
                            foreach($clubes as $club){
                                $this->_club->deleteClubs(
                                        array(
                                            'idClubBuceo' => $club['idClubBuceo']
                                        )
                                    );
                            }
                        }
                        if(count($images) > 0){
                            foreach($images as $image){
                                $this->_image->deleteFile($this->imageRoute . $image['Inombre']);
                                $this->_image->deleteFile($this->imageRouteMin . $image['Inombre']);
                            }
                        }
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
            $this->view->result = $this->buceo->getSelected($id);
        } else {
            $this->_redirect('admin/buceo/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar Información de buceo';
        $this->view->description = 'Seleccione una opción - Se eliminaran los datos de buceo, imagenes y clubes de buceo';
    }

}

