<?php

//Modelos
require_once 'Islas.php';
require_once 'Grupoislas.php';
require_once 'Imagenes.php';
require_once 'Hoteles.php';
require_once 'Sitios.php';

//Librerias
require_once 'Common.php';

class Admin_IslaController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idIsla', 'label' => 'N° Isla', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'idGrupoIsla', 'label' => 'Grupo de islas', 'order' => true),
        array('field' => 'GInombreEs', 'label' => 'Grupo de islas', 'list' => true),
        array('field' => 'ISnombreEs', 'label' => 'Nombre (Español)', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'ISnombreEn', 'label' => 'Nombre (Ingles)'),
        array('field' => 'ISdescripcionEs', 'label' => 'Descripción (Español)'),
        array('field' => 'ISdescripcionEn', 'label' => 'Descripción (Ingles)'),
        array('field' => 'ISimagenPrincipal', 'label' => 'Imagen principal'),
        array('field' => 'ISmapa', 'label' => 'Mapa'),
        );    
    var $validations = array(
        'idGrupoIsla' => 'required',
        'ISnombreEs' => 'required',
        'ISnombreEn' => 'required',
        'ISimagenPrincipal' => 'required|valid_image_file',
        'ISmapa' => 'valid_swf_file',
        'images[]' => 'valid_image_file'
        );    
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo grupo de islas', 'icon' => 'th-large', 'controller' => 'grupoisla', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los grupos de islas', 'icon' => 'list', 'controller' => 'grupoisla', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva isla', 'icon' => 'globe', 'controller' => 'isla', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las islas', 'icon' => 'list', 'controller' => 'isla', 'action' => 'index'),
        array('type' => 'link', 'label' => 'Ordenar islas', 'icon' => 'sort-by-attributes', 'controller' => 'isla', 'action' => 'order'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva información de buceo', 'icon' => 'info-sign', 'controller' => 'buceo', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las informaciones de buceo', 'icon' => 'list', 'controller' => 'buceo', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva actividad', 'icon' => 'font', 'controller' => 'actividadisla', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las actividades', 'icon' => 'list', 'controller' => 'actividadisla', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar sitios de interés', 'icon' => 'font', 'controller' => 'sitios', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los sitios', 'icon' => 'list', 'controller' => 'sitios', 'action' => 'index'),
        );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
        );
    var $response;
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
        $this->view->menuCurrent = 'isla';
        $this->view->currentIcon = 'glyphicon glyphicon-globe';
        $this->view->currentBrand = 'Íslas';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Isla creada con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Isla editada con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Isla eliminada con éxito!';
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
        $this->islas = new Model_DBTable_Islas();
        $this->grupoIsla = new Model_DBTable_Grupoislas();
        $this->imagenes = new Model_DBTable_Imagenes();
        $this->hoteles = new Model_DBTable_Hoteles();
        $this->sitios = new Model_DBTable_Sitios();
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
            $results_all = $this->islas->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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

    public function orderAction() {

        if ($this->getRequest()->isPost()) {

            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->islas->edit(array(
                    'idIsla' => $idIsla,
                    'ISorden' => $key
                    ));
            }
            
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar islas';
        $this->view->description = 'Seleccione una isla con el mouse, y use las flechas para ordenarlas como desee';
        
        $this->view->islas = $this->islas->showAll(null, 'ISorden', 'ASC');     
    }

    public function addAction() {        
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        
        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax
        $this->_session->setInputRendered(3);
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('ISimagenPrincipal');
                
                if($principalImage['ISimagenPrincipal']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'ISimagenPrincipal');
                    if($imageName){
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['ISimagenPrincipal'] = $imageName;
                    }else{
                        //Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();
                
                // $swf = $this->upload->getFileInfo('ISmapa');
                
                // if($swf['ISmapa']['error'] == 0){
                //     $fileName = $this->_image->processFile($swf, $this->upload, 'ISmapa');
                //     if($fileName){
                //         //Add filename to list of uploaded files if successful
                //         $uploadedFiles[] = $this->fileRoute . $fileName;
                //         $_POST['ISmapa'] = $fileName;                     
                //     }else{
                //         //If extensions are bad => Delete all uploaded files 
                //         $this->_image->deleteFiles($uploadedFiles);
                //         $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                //         $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                //     }
                // }
                // //clear all filters for new files
                // $this->upload->clearFilters();


                //Sends full post to database
                unset($_POST["ISmapaNone"]);
                unset($_POST["ISmapa"]);
                unset($_POST["markes"]);
                
                $idIsla = $this->islas->add($_POST);   
                
      
                if($_POST["markes"]["hoteles"]){
                    $markers_hoteles=json_decode($_POST["markes"]["hoteles"],true);
                    foreach ($markers_hoteles as $key => $value) {
                        $this->hoteles->edit(array('HOlat'=>$value["lat"],'HOlng'=>$value["lng"],'idHotel'=>$value["id"]));
                    }
                }
                
               
                if($_POST["markes"]["sitios"]){
                    $markers_sitios=json_decode($_POST["markes"]["sitios"],true);
                    foreach ($markers_sitios as $key => $value) {
                        $this->sitios->edit(array('SIlat'=>$value["lat"],'SIlng'=>$value["lng"],'idSitios'=>$value["id"]));
                    }
                }
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
                                    'idIsla' => $idIsla
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
        $this->view->formId = 'islaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nueva isla';
        $this->view->description = 'Complete el formulario para agregar una nueva isla';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }    

    public function editAction() {
        $isla = $this->islas->get($this->view->parameters['id']);
        
        $imagenes = $this->imagenes->showAll('idIsla = ' . $isla['idIsla']);
        $hoteles = $this->hoteles->showAll('idIsla = ' . $isla['idIsla']);
        foreach ($hoteles as $key => $h) {
            if(!empty($h["HOlat"]) && !empty($h["HOlng"])){

                $locations[]=array('latLng' => array(
                    'lat' => (Double)$h["HOlat"],
                    'lng' => (Double)$h["HOlng"]
                    ),
                'id'=>(int)$h["idHotel"],
                'color'=>$h["CHcolor"],
                'title' => $h["HOnombreEs"]
                );
            }
        }

        $sitios = $this->sitios->showAll('idIsla = ' . $isla['idIsla']);
      
        foreach ($sitios as $key => $h) {
            if(!empty($h["SIlat"]) && !empty($h["SIlng"])){

                $locations2[]=array('latLng' => array(
                    'lat' => (Double)$h["SIlat"],
                    'lng' => (Double)$h["SIlng"]
                    ),
                'icon'=>$h["CSIcono"],
                'id'=>(int)$h["idSitios"],
                'color'=>$h["CScolor"],
                'title' => $h["SInombreEs"]
                );
            }
        }
        $this->view->locations=$locations;
        $this->view->locations2=$locations2;
        $this->view->hoteles = $hoteles;
        $this->view->sitios = $sitios;
        
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();
        
        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax (+3 inputs on partial imageInputsEdit)
        $this->_session->setInputRendered(count($imagenes) + 3);
        $this->view->inputLimit = $this->_session->getInputLimit();
        
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;
        
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id from this isle
            unset($_POST['submit']);
            $_POST['idIsla'] = $isla['idIsla'];
            if($_POST['eliminarImagenes']){
                $eliminarImagenes = $_POST['eliminarImagenes'];
                unset($_POST['eliminarImagenes']);
            }
            if($_POST['eliminarSwf']){
                $eliminarSwf = $_POST['eliminarSwf'];
                unset($_POST['eliminarSwf']);
            }
            
            try{
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $principalImage = $this->upload->getFileInfo('ISimagenPrincipal');
                
                if($principalImage['ISimagenPrincipal']['error'] == 0){
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'ISimagenPrincipal');
                    if($imageName){
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $isla['ISimagenPrincipal']);
                        $this->_image->deleteFile($this->imageRouteMin . $isla['ISimagenPrincipal']);
                        
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['ISimagenPrincipal'] = $imageName;
                    }else{
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));                        
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();
                
                // $swf = $this->upload->getFileInfo('ISmapa');
                
                // if($swf['ISmapa']['error'] == 0){
                //     $fileName = $this->_image->processFile($swf, $this->upload, 'ISmapa');
                //     if($fileName){
                //         //Delete previous swf file
                //         $this->_image->deleteFile($this->fileRoute . $isla['ISmapa']);

                //         //Add filename to list of uploaded files if successful
                //         $uploadedFiles[] = $this->fileRoute . $fileName;
                //         $_POST['ISmapa'] = $fileName;                     
                //     }else{
                //         //If extensions are bad => Delete all uploaded files 
                //         $this->_image->deleteFiles($uploadedFiles);
                //         $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                //         $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                //     }
                // }
                // //clear all filters for new files
                // $this->upload->clearFilters();
                //Delete all flagged images if any flagged
                if($eliminarImagenes){
                    $this->_image->deleteFromTable($eliminarImagenes);
                }
                //Delete swf if flagged 
                // if($eliminarSwf){
                //     $this->_image->deleteFile($this->fileRoute . $isla['ISmapa']);
                //     $_POST['ISmapa'] = '';
                // }
                
                $_POST["ISmapa"]=json_encode($_POST["ISmapa"]);
                if($_POST["ISmapaNone"]==1){
                    $_POST["ISmapa"]='hidden';
                }else{
                    $_POST["ISmapa"]=json_encode($_POST["ISmapa"]);
                }
                
                $this->hoteles->resetLatLngByIsla($this->view->parameters['id']);
                if($_POST["markes"]["hoteles"]){
                    $markers_hoteles=json_decode($_POST["markes"]["hoteles"],true);
                    foreach ($markers_hoteles as $key => $value) {
                        $this->hoteles->edit(array('HOlat'=>$value["lat"],'HOlng'=>$value["lng"],'idHotel'=>$value["id"]));
                    }
                }
                
                $this->sitios->resetLatLngByIsla($this->view->parameters['id']);
                if($_POST["markes"]["sitios"]){
                    $markers_sitios=json_decode($_POST["markes"]["sitios"],true);
                    foreach ($markers_sitios as $key => $value) {
                        $this->sitios->edit(array('SIlat'=>$value["lat"],'SIlng'=>$value["lng"],'idSitios'=>$value["id"]));
                    }
                }


                //Sends full post to database
                unset($_POST["ISmapaNone"]);
                unset($_POST["ISmapa"]);
                unset($_POST["markes"]);
                $this->islas->edit($_POST);   
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
                                        'idIsla' => $_POST['idIsla']
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
        
        //idFormulario para validacion
        $this->view->formId = 'islaForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar isla';
        $this->view->description = 'Complete el formulario para agregar una editar la isla';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $isla;
        $this->view->images = $imagenes;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $isla = $this->islas->get($id);
                        if($isla['ISimagenPrincipal']){
                            $this->_image->deleteFile($this->imageRoute . $isla['ISimagenPrincipal']);
                            $this->_image->deleteFile($this->imageRouteMin . $isla['ISimagenPrincipal']);                            
                        }
                        $this->view->result = $this->islas->delete_row($id);
                        $images = $this->imagenes->showAll('idIsla = ' . $id);
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
            $this->view->result = $this->islas->get($id);
        } else {
            $this->_redirect('admin/isla/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar isla';
        $this->view->description = 'Seleccione una opción';
    }

}

