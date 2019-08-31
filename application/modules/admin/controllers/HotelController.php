<?php

//Modelos
require_once 'Islas.php';
require_once 'Grupoislas.php';
require_once 'Imagenes.php';
require_once 'Hoteles.php';
require_once 'Habitacion.php';
require_once 'Habitacion.php';
require_once 'Categoriahotel.php';
require_once 'ComodidadHabitacion.php';
require_once 'ComodidadHotel.php';

//Librerias
require_once 'Common.php';

class Admin_HotelController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'idHotel', 'label' => 'N° Hotel', 'list' => true, 'width' => 100, 'class' => 'id', 'order' => true),
        array('field' => 'idGrupoIsla', 'label' => 'Grupo de islas', 'order' => false),
        array('field' => 'idCategoriaHotel', 'label' => 'Categoría hotel', 'order' => false,'required'=>true),
        array('field' => 'idIsla', 'label' => 'Ísla', 'order' => false),
        array('field' => 'GInombreEs', 'label' => 'Grupo de islas', 'list' => true, 'order' => true),
        array('field' => 'ISnombreEs', 'label' => 'Ísla', 'list' => true, 'order' => true),
        array('field' => 'HOnombreEs', 'label' => 'Nombre (Español)', 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'HOnombreEn', 'label' => 'Nombre (Ingles)'),
        array('field' => 'HOdescripcionEs', 'label' => 'Descripción (Español)'),
        array('field' => 'HOdescripcionEn', 'label' => 'Descripción (Ingles)'),
        array('field' => 'HOlogo', 'label' => 'Logo'),
        array('field' => 'HOmapa', 'label' => 'Mapa'),
    );
    var $validations = array(
        'idIsla' => 'required',
        'HOnombreEs' => 'required',
        'HOnombreEn' => 'required',
        'HOlogo' => 'required|valid_image_file',
        'HOmapa' => 'valid_image_file',
        'images[]' => 'valid_image_file'
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo hotel', 'icon' => 'header', 'controller' => 'hotel', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los hoteles', 'icon' => 'list', 'controller' => 'hotel', 'action' => 'index'),
        array('type' => 'link', 'label' => 'Ordenar hoteles', 'icon' => 'sort-by-attributes', 'controller' => 'hotel', 'action' => 'order'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar Categorías de hoteles', 'icon' => 'info-sign', 'controller' => 'categoriahotel', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las categorías', 'icon' => 'list', 'controller' => 'categoriahotel', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Administrar habitaciones', 'icon' => 'bookmark text-success', 'controller' => 'hotel', 'action' => 'room'),
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );
    var $response;
    var $islas;
    var $grupoIsla;
    var $imagenes;
    var $hoteles;
    //Helpers
    var $_redirector;
    var $_image;
    var $_hotel;
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
        if (!$data) {
            $this->_redirect('/admin/login');
        } else {
            $this->view->ULoginname = $data->ULoginname;
            $this->view->UName = $data->UName;
            $this->view->ULastname = $data->ULastname;
            $this->view->IDUser = $data->IDUser;
        }

        //Vars and params
        $this->parameters = $this->_request->getParams();
        $this->view->parameters = $this->parameters;
        $this->view->controller = $this->_getParam('controller');
        $this->view->menuCurrent = 'hotel';
        $this->view->currentIcon = 'glyphicon glyphicon-header';
        $this->view->currentBrand = 'Hoteles';

        //Messages (error and success)
        //$this->warningMessage = 'Ya existe una isla con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Hotel creado con éxito!';
        $this->newRoomSuccessMessage = '<b>Información:</b> Habitaciones y comodidades guardadas con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Hotel editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Hotel eliminado con éxito!';
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
        $this->_hotel = $this->_helper->getHelper('Hotel');
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
        $this->room = new Model_DBTable_Habitacion();
        $this->comodidadesHotel = new Model_DBTable_ComodidadHotel();
        $this->comodidadesHabitacion = new Model_DBTable_ComodidadHabitacion();
        $this->categoriahotel = new Model_DBTable_Categoriahotel();

        $this->form = new Zend_Form();

        //Routes
        $this->fileRoute = PUBLIC_PATH . DS . 'files' . DS;
        $this->imageRoute = PUBLIC_PATH . DS . 'images' . DS;
        $this->imageRouteMin = PUBLIC_PATH . DS . 'images' . DS . 'm_';

        //Extra scripts
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
    }

    public function indexAction() {
        if ($this->_getParam('search', '') != '') {
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
            $results_all = $this->hoteles->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
        if ($statusBar) {
            $this->view->statusBar = $statusBar;
        }
    }
    
    public function roomAction(){        
        $hotel = $this->hoteles->get($this->view->parameters['id']);
        //Setting how many rooms already rendered
        $habitaciones = $this->_hotel->getHabitaciones($hotel['idHotel']);
        $this->_session->setHabitacionRendered(count($habitaciones));
        //Setting how many hotel's features already rendered
        $comodidadesHotel = $this->_hotel->getComodidadesHotel($hotel['idHotel']);
        $this->_session->setComodidadHotelRendered(count($comodidadesHotel));
        //Setting how many room's features already rendered
        $comodidadesHabitacion = $this->_hotel->getComodidadesHabitacion($hotel['idHotel']);
        $this->_session->setComodidadHabitacionRendered(count($comodidadesHabitacion));
        
        if ($this->getRequest()->isPost()) {         
            //Deleting variables from post
            unset($_POST['submit']);
            
            //Set variable for rooms
            if ($_POST['habitacion']) {
                $habitacionesNew = $_POST['habitacion'];
                unset($_POST['habitacion']);
            }
            if ($_POST['habitacionOld']) {
                $habitacionesOld = $_POST['habitacionOld'];
                unset($_POST['habitacionOld']);
            }
            if ($_POST['eliminarHabitaciones']) {
                $eliminarHabitaciones = $_POST['eliminarHabitaciones'];
                unset($_POST['eliminarHabitaciones']);
            }
            
            //Set variable for room's features
            if ($_POST['comodidadhabitacion']) {
                $comodidadesHabitacionNew = $_POST['comodidadhabitacion'];
                unset($_POST['comodidadhabitacion']);
            }
            if ($_POST['comodidadhabitacionOld']) {
                $comodidadesHabitacionOld = $_POST['comodidadhabitacionOld'];
                unset($_POST['comodidadhabitacionOld']);
            }
            if ($_POST['eliminarComodidadesHabitacion']) {
                $eliminarComodidadesHabitacion = $_POST['eliminarComodidadesHabitacion'];
                unset($_POST['eliminarComodidadesHabitacion']);
            }
            
            //Set variable for hotel's features
            if ($_POST['comodidadhotel']) {
                $comodidadesHotelNew = $_POST['comodidadhotel'];
                unset($_POST['comodidadhotel']);
            }
            if ($_POST['comodidadhotelOld']) {
                $comodidadesHotelOld = $_POST['comodidadhotelOld'];
                unset($_POST['comodidadhotelOld']);
            }
            if ($_POST['eliminarComodidadesHotel']) {
                $eliminarComodidadesHotel = $_POST['eliminarComodidadesHotel'];
                unset($_POST['eliminarComodidadesHotel']);
            }
            
            $idHotel = $hotel['idHotel'];
            
            //add, edit and delete rooms
            if ($habitacionesNew) {
                $this->_hotel->addHabitaciones($habitacionesNew, $idHotel);
            }
            if ($habitacionesOld) {
                $this->_hotel->editHabitaciones($habitacionesOld);
            }                
            if ($eliminarHabitaciones) {
                $this->_hotel->deleteHabitaciones($eliminarHabitaciones);
            }
            //room features
            if ($comodidadesHabitacionNew) {
                $this->_hotel->addComodidadesHabitacion($comodidadesHabitacionNew, $idHotel);
            }
            if ($comodidadesHabitacionOld) {
                $this->_hotel->editComodidadesHabitacion($comodidadesHabitacionOld);
            }                
            if ($eliminarComodidadesHabitacion) {
                $this->_hotel->deleteComodidadesHabitacion($eliminarComodidadesHabitacion);
            }
            
            //hotel features
            if ($comodidadesHotelNew) {
                $this->_hotel->addComodidadesHotel($comodidadesHotelNew, $idHotel);
            }        
            if ($comodidadesHotelOld) {
                $this->_hotel->editComodidadesHotel($comodidadesHotelOld);
            }                
            if ($eliminarComodidadesHotel) {
                $this->_hotel->deleteComodidadesHotel($eliminarComodidadesHotel);
            }  
            
            //Manejo avisos y redirecciono
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newRoomSuccessMessage));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }
        
        //Datos de BD a la vista
        $this->view->grupoIsla = $this->grupoIsla->showAll();
        $this->view->islaActual = $this->islas->get($hotel['idIsla']);
        $this->view->islas = $this->islas->showAll('idGrupoIsla = ' . $this->view->islaActual['idGrupoIsla']);
        $this->view->comodidadesHabitacion = $comodidadesHabitacion;
        $this->view->comodidadesHotel = $comodidadesHotel;
        $this->view->habitaciones = $habitaciones;
        $this->view->result = $hotel;

        //idFormulario para validacion
        $this->view->formId = 'hotelForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'bookmark';
        $this->view->title = 'Comodidades y habitaciones del hotel';
        $this->view->description = 'En este formulario podrá agregar y modificar información sobre comodidades del hotel y sus habitaciones.';

        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $hotel;
    }

    public function addAction() {        
        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();

        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax
        $this->_session->setInputRendered(3);
        //Setting how many activities already rendered
        $this->_session->setActividadHotelRendered(0);
        //Setting how many restaurants already rendered
        $this->_session->setRestobarRendered(0);
        //Setting how many events already rendered
        $this->_session->setEventoHotelRendered(0);

        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;

        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            //Set variable for activities
            if ($_POST['actividad']) {
                $actividades = $_POST['actividad'];
                unset($_POST['actividad']);
            }
            if ($_POST['restobar']) {
                $restoBares = $_POST['restobar'];
                unset($_POST['restobar']);
            }
            if ($_POST['evento']) {
                $eventos = $_POST['evento'];
                unset($_POST['evento']);
            }

            try {
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                $logo = $this->upload->getFileInfo('HOlogo');

                if ($logo['HOlogo']['error'] == 0) {
                    $imageName = $this->_image->processImage($logo, $this->upload, 'HOlogo');
                    if ($imageName) {
                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['HOlogo'] = $imageName;
                    } else {
                        //Delete all uploaded files
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();

                $map = $this->upload->getFileInfo('HOmapa');

                if ($map['HOmapa']['error'] == 0) {
                    $fileName = $this->_image->processImage($map, $this->upload, 'HOmapa');
                    if ($fileName) {
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $hotel['HOmapa']);
                        $this->_image->deleteFile($this->imageRouteMin . $hotel['HOmapa']);

                        //Add filename to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $fileName;
                        $uploadedFiles[] = $this->imageRouteMin . $fileName;
                        $_POST['HOmapa'] = $fileName;
                    } else {
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();

                $idHotel = $this->hoteles->add($_POST);

                //add actividades to this island 
                if ($actividades) {
                    $this->_hotel->addActividades($actividades, $idHotel);
                }
                //add restobares to this island 
                if ($restoBares) {
                    $this->_hotel->addRestobares($restoBares, $idHotel);
                }
                //add eventos to this island 
                if ($eventos) {
                    $this->_hotel->addEventos($eventos, $idHotel);
                }

                $images = $this->upload->getFileInfo('images');

                if (count($images) > 0) {
                    foreach ($images as $keyImage => $image) {
                        if ($images[$keyImage]['error'] == 0) {
                            $fileName = $this->_image->processImage($images, $this->upload, $keyImage);
                            if ($fileName) {
                                //Add filename to list of uploaded files if successful
                                $uploadedFiles[] = $this->imageRoute . $fileName;
                                //Add image to image table
                                $this->imagenes->add(array(
                                    'Inombre' => $fileName,
                                    'idHotel' => $idHotel
                                ));
                            } else {
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
            } catch (Zend_Exception $exception) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }

        //Datos de BD a la vista
        $this->view->grupoIsla = $this->grupoIsla->showAll();
        $this->view->categoriaHotel = $this->categoriahotel->showAll();

        //idFormulario para validacion
        $this->view->formId = 'hotelForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo hotel';
        $this->view->description = 'Complete el formulario para agregar un nuevo hotel a una isla en particular';

        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function editAction() {
        $hotel = $this->hoteles->get($this->view->parameters['id']);
        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax
        $imagenes = $this->imagenes->showAll('idHotel = ' . $hotel['idHotel']);
        $this->_session->setInputRendered(count($imagenes));
        //Setting how many activities already rendered
        $actividades = $this->_hotel->getActividades($hotel['idHotel']);
        $this->_session->setActividadHotelRendered(count($actividades));
        //Setting how many restaurants already rendered
        $restobares = $this->_hotel->getRestobares($hotel['idHotel']);
        $this->_session->setRestobarRendered(count($restobares));
        //Setting how many events already rendered
        $eventos = $this->_hotel->getEventos($hotel['idHotel']);
        $this->_session->setEventoHotelRendered(count($eventos));

        //Sets an array for uploaded files to prevent useless files upload to server if transaction fails
        $uploadedFiles = array();

        //Setting how many images already uploaded and Clears the session already rendered for inputs added by ajax (+3 inputs on partial imageInputsEdit)
        $this->_session->setInputRendered(count($imagenes) + 3);
        $this->view->inputLimit = $this->_session->getInputLimit();
        $this->view->categoriaHotel = $this->categoriahotel->showAll();
        $this->parameters = $this->_getAllParams();
        $this->view->parameters = $this->parameters;

        if ($this->getRequest()->isPost()) {
            //Deleting variables from post and setting id
            unset($_POST['submit']);
            unset($_POST['idGrupoIsla']);
            $_POST['idHotel'] = $hotel['idHotel'];
            //Set variable for images to delete
            if ($_POST['eliminarImagenes']) {
                $eliminarImagenes = $_POST['eliminarImagenes'];
                unset($_POST['eliminarImagenes']);
            }
            //Set variable for activities to delete
            if ($_POST['eliminarActividades']) {
                $eliminarActividades = $_POST['eliminarActividades'];
                unset($_POST['eliminarActividades']);
            }
            //Set variable for new activities
            if ($_POST['actividad']) {
                $actividadesNew = $_POST['actividad'];
                unset($_POST['actividad']);
            }
            //Set variable for old activities
            if ($_POST['actividadOld']) {
                $actividadesOld = $_POST['actividadOld'];
                unset($_POST['actividadOld']);
            }

            //Set variable for events to delete
            if ($_POST['eliminarEventos']) {
                $eliminarEventos = $_POST['eliminarEventos'];
                unset($_POST['eliminarEventos']);
            }
            //Set variable for new events
            if ($_POST['evento']) {
                $eventosNew = $_POST['evento'];
                unset($_POST['evento']);
            }
            //Set variable for old events
            if ($_POST['eventoOld']) {
                $eventosOld = $_POST['eventoOld'];
                unset($_POST['eventoOld']);
            }

            //Set variable for restobares to delete
            if ($_POST['eliminarRestobar']) {
                $eliminarRestobar = $_POST['eliminarRestobar'];
                unset($_POST['eliminarRestobar']);
            }
            //Set variable for new restobares
            if ($_POST['restobar']) {
                $restobarNew = $_POST['restobar'];
                unset($_POST['restobar']);
            }
            //Set variable for old restobares
            if ($_POST['restobarOld']) {
                $restobarOld = $_POST['restobarOld'];
                unset($_POST['restobarOld']);
            }
            
            try {
                $this->upload = new Zend_File_Transfer_Adapter_Http();
                //Delete all flagged images if any flagged
                if ($eliminarImagenes) {
                    $this->_image->deleteFromTable($eliminarImagenes);
                }
                //Editing activities
                if ($actividadesOld) {
                    $this->_hotel->editActividades($actividadesOld);
                }
                //Add new activities            
                if ($actividadesNew) {
                    $this->_hotel->addActividades($actividadesNew, $hotel['idHotel']);
                }
                //Deleting unwanted activities                
                if ($eliminarActividades) {
                    $this->_hotel->deleteActividades($eliminarActividades);
                }
                
                //Editing events
                if ($eventosOld) {
                    $this->_hotel->editEventos($eventosOld);
                }
                //Add new events            
                if ($eventosNew) {
                    $this->_hotel->addEventos($eventosNew, $hotel['idHotel']);
                }
                //Deleting unwanted events                
                if ($eliminarEventos) {
                    $this->_hotel->deleteEventos($eliminarEventos);
                }
                
                //Editing restobares
                if ($restobarOld) {
                    $this->_hotel->editRestobares($restobarOld);
                }
                //Add new restobares            
                if ($restobarNew) {
                    $this->_hotel->addRestobares($restobarNew, $hotel['idHotel']);
                }
                //Deleting unwanted restobares                
                if ($eliminarRestobar) {
                    $this->_hotel->deleteRestobares($eliminarRestobar);
                }
                
                $principalImage = $this->upload->getFileInfo('HOlogo');

                if ($principalImage['HOlogo']['error'] == 0) {
                    $imageName = $this->_image->processImage($principalImage, $this->upload, 'HOlogo');
                    if ($imageName) {
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $hotel['HOlogo']);
                        $this->_image->deleteFile($this->imageRouteMin . $hotel['HOlogo']);

                        //Add filename (and miniature) to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $imageName;
                        $uploadedFiles[] = $this->imageRouteMin . $imageName;
                        $_POST['HOlogo'] = $imageName;
                    } else {
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();

                $map = $this->upload->getFileInfo('HOmapa');

                if ($map['HOmapa']['error'] == 0) {
                    $fileName = $this->_image->processImage($map, $this->upload, 'HOmapa');
                    if ($fileName) {
                        //Delete previous principal image if successful
                        $this->_image->deleteFile($this->imageRoute . $hotel['HOmapa']);
                        $this->_image->deleteFile($this->imageRouteMin . $hotel['HOmapa']);

                        //Add filename to list of uploaded files if successful
                        $uploadedFiles[] = $this->imageRoute . $fileName;
                        $uploadedFiles[] = $this->imageRouteMin . $fileName;
                        $_POST['HOmapa'] = $fileName;
                    } else {
                        //If extensions are bad => Delete all uploaded files 
                        $this->_image->deleteFiles($uploadedFiles);
                        $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->badExtension));
                        $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                    }
                }
                //clear all filters for new files
                $this->upload->clearFilters();
                
                //Sends full post to database
                $this->hoteles->edit($_POST);
                if ($_FILES['images']) {
                    $images = $this->upload->getFileInfo('images');
                    if (count($images) > 0) {
                        foreach ($images as $keyImage => $image) {
                            if ($images[$keyImage]['error'] == 0) {
                                $fileName = $this->_image->processImage($images, $this->upload, $keyImage);
                                if ($fileName) {
                                    //Add filename to list of uploaded files if successful
                                    $uploadedFiles[] = $this->imageRoute . $fileName;
                                    $uploadedFiles[] = $this->imageRouteMin . $fileName;
                                    //Add image to image table
                                    $this->imagenes->add(array(
                                        'Inombre' => $fileName,
                                        'idHotel' => $_POST['idHotel']
                                    ));
                                } else {
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
            } catch (Zend_Exception $exception) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exception->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }
        }

        //Datos de BD a la vista
        $this->view->grupoIsla = $this->grupoIsla->showAll();
        $this->view->islaActual = $this->islas->get($hotel['idIsla']);
        $this->view->islas = $this->islas->showAll('idGrupoIsla = ' . $this->view->islaActual['idGrupoIsla']);
        $this->view->actividades = $actividades;
        $this->view->restobares = $restobares;
        $this->view->eventos = $eventos;
        //idFormulario para validacion
        $this->view->formId = 'hotelForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar hotel';
        $this->view->description = 'Complete el formulario para editar el hotel';

        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $hotel;
        $this->view->images = $imagenes;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $hotel = $this->hoteles->get($id);
                        if ($hotel['HOlogo']) {
                            $this->_image->deleteFile($this->imageRoute . $isla['HOlogo']);
                            $this->_image->deleteFile($this->imageRouteMin . $isla['HOlogo']);
                        }
                        if ($hotel['HOmapa']) {
                            $this->_image->deleteFile($this->imageRoute . $isla['HOmapa']);
                            $this->_image->deleteFile($this->imageRouteMin . $isla['HOmapa']);
                        }
                        $this->view->result = $this->hoteles->delete_row($id);
                        $images = $this->imagenes->showAll('idHotel = ' . $id);
                        if (count($images) > 0) {
                            foreach ($images as $image) {
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
            $this->view->result = $this->hoteles->get($id);
        } else {
            $this->_redirect('admin/hotel/');
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar hotel';
        $this->view->description = 'Seleccione una opción';
    }

    public function orderAction() {

        if ($this->getRequest()->isPost()) {

            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->hoteles->edit(array(
                    'idHotel' => $idIsla,
                    'HOrder' => $key
                    ));
            }
            
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar hoteles';
        $this->view->description = 'Seleccione un hotel con el mouse, y use las flechas para ordenarlas como desee';
        
        $this->view->islas = $this->hoteles->showAll(null, 'HOrder', 'ASC');  
    }

    public function roomorderAction() {

        if ($this->getRequest()->isPost()) {

            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->room->edit(array(
                    'idHabitacion' => $idIsla,
                    'HHOrder' => $key
                    ));
            }
            
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->hotel = $this->hoteles->get($this->view->parameters['id']);
        $habitaciones = $this->_hotel->getHabitaciones($this->view->hotel['idHotel']);
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar habitaciones de hotel '.$this->view->hotel["HOnombreEs"];
        $this->view->description = 'Seleccione una habitación con el mouse, y use las flechas para ordenarlas como desee';
        
        $this->view->islas = $habitaciones;  
    }
    public function roomorder2Action() {

        if ($this->getRequest()->isPost()) {

            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->comodidadesHabitacion->edit(array(
                    'idComodidadHabitacion' => $idIsla,
                    'CHorden' => $key
                    ));
            }
            
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->hotel = $this->hoteles->get($this->view->parameters['id']);
        $habitaciones = $this->_hotel->getComodidadesHabitacion($this->view->hotel['idHotel']);
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar comodidades de la habitacion '.$this->view->hotel["HOnombreEs"];
        $this->view->description = 'Seleccione una comodidad con el mouse, y use las flechas para ordenarlas como desee';
        
        $this->view->islas = $habitaciones;  
    }
    public function roomorder3Action() {

        if ($this->getRequest()->isPost()) {

            foreach($_POST['idIslas'] as $key => $idIsla){
                $this->comodidadesHotel->edit(array(
                    'idComodidadHotel' => $idIsla,
                    'CHOorden' => $key
                    ));
            }
            
        }

        //Icono, titulo y descripcion para el formulario
        $this->view->hotel = $this->hoteles->get($this->view->parameters['id']);
        $habitaciones = $this->_hotel->getComodidadesHotel($this->view->hotel['idHotel']);
        $this->view->icon = 'sort-by-attributes';
        $this->view->title = 'Ordenar comodidades del hotel '.$this->view->hotel["HOnombreEs"];
        $this->view->description = 'Seleccione una comodidad con el mouse, y use las flechas para ordenarlas como desee';
        
        $this->view->islas = $habitaciones;  
    }
}
