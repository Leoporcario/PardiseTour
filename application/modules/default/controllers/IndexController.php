<?php

//Modelos
require_once 'Modules.php';
require_once 'Islas.php';
require_once 'Grupoislas.php';
require_once 'Imagenes.php';
require_once 'ActividadIsla.php';
require_once 'Buceo.php';
require_once 'Clubes.php';
require_once 'Hoteles.php';
require_once 'Suscriptors.php';
require_once 'Archivos.php';
require_once 'ActividadHotel.php';
require_once 'EventoHotel.php';
require_once 'Restobar.php';
require_once 'Habitacion.php';
require_once 'ComodidadHabitacion.php';
require_once 'ComodidadHotel.php';
require_once 'EventoIsla.php';
require_once 'Suscriptors.php';
require_once 'News.php';
require_once 'Sitios.php';
require_once 'Categoriahotel.php';
require_once 'Categoriasitios.php';
require_once 'ArchivoPerfil.php';

require_once 'Common.php';

//Librerias

class IndexController extends Zend_Controller_Action {

    public function init() {
        //Datos de login y sesion
        $options = array(
            'layout' => 'default',
            'layoutPath' => '../application/layouts/scripts/'
            );
        Zend_Layout::startMvc($options);

        //Vars and params
        $this->parameters = $this->_request->getParams();
        $this->view->parameters = $this->parameters;
        $this->view->controller = $this->_getParam('controller');
        $this->view->menuCurrent = 'index';
        $this->view->currentIcon = 'glyphicon glyphicon-home';
        $this->view->currentBrand = 'Tahiti Paradise';
        $this->view->noNavbar = true;

        //Gets sessions and autenticates
        $this->_session = $this->_helper->getHelper('PublicUser');

        //Obtengo lang
        if (isset($this->parameters['lang']) && $this->parameters['lang'] != '') {
            $this->_session->setLang($this->parameters['lang']);
            $this->_redirect("/index");
        }
        //Obtengo lang y lo paso a la vista
        $lang = $this->_session->getLang();
        $this->lang = $lang;
        $this->view->lang = $lang;
        //Obtengo datos de sesion
        $data = $this->_session->getSession();

        //Habilito o no el menu segun el login
        if (!$data['log']) {
            $this->view->enableMenu = false;
        } else {
            $this->view->enableMenu = true;
            $this->view->user = $data;
        }

        //Mando firstUse y seteo en false
        $this->view->firstUse = $data['firstUse'];
        $this->_session->setFirstUse(0);

        //Messages (error and success)
        $this->warningMessage = 'Ya existe un suscriptor con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->loginSuccess = '<b>Información:</b> Bienvenido!';
        $this->logoutSuccess = '<b>Información:</b> Sesión cerrada.';
        $this->loginError = '<b>Información:</b> Email/Contraseña incorrectos.!';

        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;

        //Modelos
        $this->modules = new Model_DBTable_Modules();

        //Partials
        //Some extra scripts
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . "/css/index.css");
    }

    public function loginAction() {
        try {
            if ($this->getRequest()->isPost()) {
                $suscriptor = $this->_session->login($_POST);
                $this->_session->setLang($suscriptor["lang"]);
                if ($suscriptor) {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => 'Bienvenido <strong>' . $suscriptor['SNombre'] . '</strong>!'));
                    $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'success'));
                } else {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->loginError));
                    $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'danger'));
                }
            } else {
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Acción inválida!'));
                $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'danger'));
            }
        } catch (Zend_Exception $exc) {

            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'danger'));
        }
    }

    public function logoutAction() {

        try {
            $this->_session->logout();
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->logoutSuccess));
            $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'success'));
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'danger'));
        }
    }

    //Home
    public function indexAction() {
        try { 
            $results_all = Model_DBTable_News::getInstance()->showAll(null,'NOrden', 'ASC');
            $paginator = Zend_Paginator::factory($results_all);
            
            $paginator->setItemCountPerPage(6)
            ->setCurrentPageNumber($this->_getParam('page', 1))
            ->setPageRange(PAGERANGE);

            $this->view->news = $paginator;

        } catch (Zend_Exception $exc) {
            exit($exc->getMessage());
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }

        //Sending params and vars to view
        $this->view->messages = $this->messages;
        if ($statusBar) {
            $this->view->statusBar = $statusBar;
        }
    }

    //Islas
    public function islandsAction() {
        try {
            //Datos base a la vista
            $this->view->menuCurrent = "islas";
            $this->view->action = "islas";

            //Obtengo el ID de la isla y lo mando a la vista
            $isleId = $this->parameters['isle'];
            if (!isset($isleId) || $isleId < 0 || !is_numeric($isleId)) {
                $islands = Model_DBTable_Islas::getInstance()->showAll();
                $isleId = $islands[0]['idIsla'];
            }
            $this->view->activeIsleId = $isleId;

            //Obtengo isla, imagenes, info de buceo, actividades y grupo de islas
            $isle = Model_DBTable_Islas::getInstance()->get($isleId);
            $images = Model_DBTable_Imagenes::getInstance()->showAll("idIsla = $isleId");
            $isleGroup = Model_DBTable_Grupoislas::getInstance()->get($isle['idGrupoIsla']);
            $activities = Model_DBTable_ActividadIsla::getInstance()->showAll("idIsla = $isleId");
            $scubaInfo = Model_DBTable_Buceo::getInstance()->showAll("idIsla = $isleId");
            $scubaInfo = $scubaInfo[0];

            //Mando a la vista
            $this->view->isle = $isle;
            $this->view->firstImage = $images[0];
            $this->view->images = $images;
            $this->view->isleGroup = $isleGroup;
            $this->view->activities = $activities;
            $this->view->scubaInfo = $scubaInfo;

            // $sitios = $this->sitios->showAll('idIsla = ' . $isla['idIsla']);
            $hoteles = Model_DBTable_Hoteles::getInstance()->showAll('idIsla = ' . $isleId);
            $ver_mas["Es"]="Ver más";
            $ver_mas["En"]="More";
            foreach ($hoteles as $key => $h) {
                if(!empty($h["HOlat"]) && !empty($h["HOlng"])){
                    
                    $locations[]=array('latLng' => array(
                        'lat' => (Double)$h["HOlat"],
                        'lng' => (Double)$h["HOlng"]
                        ),
                    'id'=>(int)$h["idHotel"],
                    'color'=>$h["CHcolor"],
                    'category'=>$h["IDCategoriaHotel"],
                    'nombre'=>$h["CHnombre".$this->lang],
                    'picture'=>$this->view->baseUrl()."/images/m_".$h["HOlogo"],
                    'label'=>$ver_mas[$this->lang],
                    'title' => $h["HOnombre".$this->lang]
                    );
                }
            }
            $modelCategoriaHotel=new Model_DBTable_Categoriahotel();
            $this->view->categoriasHotel=$modelCategoriaHotel->showAll();
            

            $modelCategoriaSitio=new Model_DBTable_Categoriasitios();
            $this->view->categoriasSitios=$modelCategoriaSitio->showAll();


            $this->view->locations=$locations;

            $sitios = Model_DBTable_Sitios::getInstance()->showAll('idIsla = ' . $isleId);
            foreach ($sitios as $key => $h) {
                if(!empty($h["SIlat"]) && !empty($h["SIlng"])){

                    $locations2[]=array('latLng' => array(
                        'lat' => (Double)$h["SIlat"],
                        'lng' => (Double)$h["SIlng"]
                        ),
                    'id'=>(int)$h["idSitios"],
                    'color'=>$h["CScolor"],
                    'icon'=>$h["CSIcono"],

                    'category'=>$h["IDCategoriaSitio"],
                    'nombre'=>$h["CSnombre".$this->lang],

                    'picture'=>$this->view->baseUrl()."/images/m_".$h["SIimagen"],
                    'label'=>$ver_mas[$this->lang],
                    'title' => $h["SInombre".$this->lang]
                    );
                }
            }
            $this->view->locations2=$locations2;
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }

    //Hoteles
    public function hotelsAction() {
        try {
            //Datos base a la vista
            $this->view->menuCurrent = "hoteles";
            $this->view->action = 'hotels';

            if($this->parameters['isle']){

                //Obtengo el ID del hotel y lo mando a la vista
                $isleId = $this->parameters['isle'];
                
                if (isset($isleId) && $isleId > 0 && is_numeric($isleId)) {

                    $hotelList = Model_DBTable_Hoteles::getInstance()->showAll('idIsla = ' . $isleId);

                    //Si no encuentro nada, redirecciono
                    if(!count($hotelList) > 0){

                        $this->_helper->flashMessenger->addMessage(array(
                            'type' => 'info', 
                            'message' => ($this->lang == 'Es') ? 'No se encontraron hoteles para la isla seleccionada.' : 'No hotels were found for the selected island.' ,
                            ));
                        
                        $this->_redirector->gotoSimple('islands', 'index', null, array(
                            'status' => 'info',
                            'isle' => $isleId
                            ));
                        
                    }
                    
                    $hotelId = $hotelList[0]['idHotel'];
                }
                
            }else{

                //Obtengo el ID del hotel y lo mando a la vista
                $hotelId = $this->parameters['hotel'];                
                
                if (!isset($hotelId) || $hotelId < 0 || !is_numeric($hotelId)) {
                    $hotels = Model_DBTable_Hoteles::getInstance()->showAll();
                    $hotelId = $hotels[0]['idHotel'];
                }
                
            }
            
            $this->view->activeHotelId = $hotelId;
            
            //Obtengo hotel desde la bd
            $hotel = Model_DBTable_Hoteles::getInstance()->get($hotelId);
            $images = Model_DBTable_Imagenes::getInstance()->showAll("idHotel = " . $hotelId);
            $isle = Model_DBTable_Islas::getInstance()->get($hotel['idIsla']);
            $hotelList = Model_DBTable_Hoteles::getInstance()->showAll('idIsla = ' . $isle['idIsla']);

            //Mando a la vista
            $this->view->hotel = $hotel;
            $this->view->hotelList = $hotelList;
            $this->view->isle = $isle;
            $this->view->activeIsleId = $isle['idIsla'];
            $this->view->firstImage = $images[0];
            $this->view->images = $images;
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }

    //Hoteles
    public function newsAction() {
        try {
            //Datos base a la vista
            $this->view->menuCurrent = "news";
            $this->view->action = 'islas';

            //Obtengo el ID del hotel y lo mando a la vista
            $newsId = $this->parameters['n'];
            if (isset($newsId) && $newsId > 0 && is_numeric($newsId)) {

                $news = Model_DBTable_News::getInstance()->get($newsId);
                
            } else {

                $this->_redirect('index');
                
            }

            //Mando a la vista
            $this->view->news = $news;
            
        } catch (Zend_Exception $exc) {

            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
            
        }
    }

    //SingleHotel
    public function singlehotelAction() {
        $this->_helper->layout->disableLayout();
        if ($this->_request->isXmlHttpRequest()) {
            //Obtengo el ID del hotel y lo mando a la vista
            $hotelId = $this->parameters['hotel'];
            
            if (isset($hotelId) && $hotelId > 0 && is_numeric($hotelId)) {

                $hotel = Model_DBTable_Hoteles::getInstance()->get($hotelId);
                
            } else {

                $this->_redirect('index');
                
            }
            
            $images = Model_DBTable_Imagenes::getInstance()->showAll("idHotel = " . $hotelId);
            $this->view->hotel = $hotel;
            $this->view->images = $images;
            $this->view->firstImage = $images[0];
        } else {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'No se encuentra la página solicitada'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }

    //Eventos
    public function eventsAction() {
        try {
            //Datos base a la vista
            $this->view->menuCurrent = "eventos";
            $this->view->action = 'hotels';

            //Obtengo los eventos destacados
            $mainEvents = Model_DBTable_EventoIsla::getInstance()->showAll('esDestacado = 1');
            //Mando a la vista
            $this->view->mainEvents = $mainEvents;
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }

    //SingleMonth
    public function singlemonthAction() {
        $this->_helper->layout->disableLayout();
        if ($this->_request->isXmlHttpRequest()) {
            //Obtengo el ID del hotel y lo mando a la vista
            $month = $this->parameters['month'];
            
            if (isset($month) && $month > 0 && is_numeric($month)) {

                $events = Model_DBTable_EventoIsla::getInstance()->showAll('EImes = ' . $month);
                
            } else {

                $this->_redirect('index');
                
            }
            $this->view->events = $events;
        } else {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'No se encuentra la página solicitada'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }
    
    //Cruises
    public function cruisesAction(){
        try {

            $this->view->menuCurrent = "cruceros";
            $this->view->action = 'cruises';
            
        } catch (Zend_Exception $exc) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
        }
    }
    
    //Transportation
    public function transportationAction(){
        try {

            $this->view->menuCurrent = "transporte";
            $this->view->action = 'transportation';
            
        } catch (Zend_Exception $exc) {

            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ocurrio algun error buscando los modulos instalados.'));
            $this->_redirector->gotoSimple('index', 'user', null, array('status' => 'danger'));
            
        }
    }

    //Files
    public function filesAction() {
        $session = $this->_session->getSession();

        $this->view->action = "files";

        if ($session['log'] == 1) {
            try {
                //Obtengo suscriptor
                $suscriptor = Model_DBTable_Suscriptors::getInstance()->get($session['id']);
                
                //Obtengo archivos para este suscriptor
                // $permisos = Model_DBTable_Archivos::getInstance()->getArchivo($suscriptor);
                $archivos = Model_DBTable_ArchivoPerfil::getInstance()->getByPerfil($suscriptor["SIDPerfil"],$this->lang);
                //Buscamos los archivos con cada permiso
                //Paso archivos a la vista con paginacion
                // if (count($archivos) > 0) {
                    //Agrego campos y acciones
                    $this->view->fields = array(
                        array('field' => 'Anombre' . $this->lang, 'label' => 'Nombre', 'list' => true, 'order' => true),
                        array('field' => 'Adescripcion' . $this->lang, 'label' => 'Descripcion', 'list' => true, 'search' => false, 'order' => false),
                        array('field' => 'AfechaCarga', 'label' => 'Fecha de carga', 'list' => true, 'search' => false, 'order' => true, 'width' => '150'),
                        );
                    $paginator = Zend_Paginator::factory($archivos);
                    $paginator->setItemCountPerPage(COUNTPERPAGE)
                    ->setCurrentPageNumber($this->_getParam('page', 1))
                    ->setPageRange(PAGERANGE);

                    $this->view->enableSearch = true;
                    $this->view->archivos = $paginator;
                // } else {
                //     $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Su cuenta no tiene archivos disponibles.'));
                //     $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'warning'));
                // }
            } catch (Zend_Exception $exc) {
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'No se encontraron archivos disponibles.'));
                $this->_redirector->gotoSimple('index', 'files', null, array('status' => 'warning'));
            }
        } else {
            $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Acceso restringido solo para usuarios del sitio.'));
            $this->_redirector->gotoSimple('index', 'index', null, array('status' => 'warning'));
        }
    }
    
    //Contact
    public function contactAction() {

        $this->view->action = 'contact';

        if ($this->getRequest()->isPost()) {

            //Elimino submit y otros no utiles
            
            //Llamo al modelo y envio mail
            try {
                /* EMAIL */
                Zend_Layout::startMvc(array('layout' => 'email', 'layoutPath' => '../application/layouts/scripts/'));
                $layout = $this->_helper->layout->getLayoutInstance();
                $this->view->post=$_POST;
                $layout->content = $this->view->render('emails/query.phtml');
                $html =  $layout->render();
                $this->_helper->Mail->sendEmail($html, "Consulta enviada desde la web");

                // $headers = "From: " . strip_tags($_POST['SEmail']) . "\r\n";
                // $headers .= "Reply-To: ". strip_tags($_POST['SEmail']) . "\r\n";
                // $headers .= "MIME-Version: 1.0\r\n";
                // $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                
                // $subject = ($this->lang == 'Es') ? 'Tahiti Paradise - Consulta desde nuestra web' : 'Tahiti Paradise - Message sent from our web' ;
                // $body = '<html><body>';
                // $body .= "<strong style='color:#2D439C'>Tahiti Paradise - Mensaje enviado desde la web. </strong><br />";
                // $body .= "Enviado por: <strong>" . $_POST['SNombre'] . "</strong> | Email : <strong>" . $_POST['SEmail'] . "</strong><br />";
                // $body .= "Empresa: " . (isset($_POST['SEmpresa']) && $_POST['SEmpresa'] != '') ? $_POST['SEmpresa'] : 'No indica.' ;
                // $body .= "<br />";
                // $body .= "Telefono: " . (isset($_POST['STelefono']) && $_POST['STelefono'] != '') ? $_POST['STelefono'] : 'No indica.' ;
                // $body .= "<br />";
                // $body .= "<strong>Consulta: </strong>" . $_POST['SConsulta'];
                // $body .= '</body></html>';
                

                // //Envio el email
                // mail(
                //     'inbound@tahitiparadise.com', 
                //     $subject, 
                //     $body, 
                //     $headers
                //     );
                
            } catch (Zend_Exception $exc) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exc->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }

            //Manejo avisos y redirecciono
            $message = ($this->lang == 'Es') ? 'Consulta enviada con éxito. Responderémos a la brevedad!' : 'Message successfully sent! We will answer as soon as possible.' ;
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $message));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }
        
    }

    //Create account
    public function accountAction() {
        $this->view->action = 'account';
        
        if ($this->getRequest()->isPost()) {
            //Si las contraseñas no son iguales
            if ($_POST['UPassword'] != $_POST['password2']) {
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Atención: Las contraseñas ingresadas no son iguales'));
                $this->_redirector->gotoSimple('add', null, null, array('id' => $this->parameters['id'], 'status' => 'warning'));
            }
            //Elimino submit y otros no utiles
            unset($_POST['password2']);
            unset($_POST['submit']);
            $_POST["lang"]=$this->lang;
            if ($_POST['UPassword']) {
                $_POST['SPassword'] = $_POST['UPassword'];
                unset($_POST['UPassword']);
            }
            //Llamo al modelo y guardo los datos
            try {
                if (count(Model_DBTable_Suscriptors::getInstance()->showAll('SEmail = "' . $_POST['SEmail'] . '"')) < 1) {
                    $id = Model_DBTable_Suscriptors::getInstance()->add($_POST);
                    // exit();
                    /* EMAIL */
                    Zend_Layout::startMvc(array('layout' => 'email', 'layoutPath' => '../application/layouts/scripts/'));
                    $layout = $this->_helper->layout->getLayoutInstance();
                    $this->view->post=$_POST;
                    $this->view->id=$id;
                    $layout->content = $this->view->render('emails/account.phtml');
                    $html =  $layout->render();
                    $this->_helper->Mail->sendEmail($html, "Creacion de cuentas");
                    // exit("ok");
                    // mail('info@tahitiparadise.com', 'Tahiti Paradise - Nuevo usuario', 'Se creo un nuevo usuario. Ingrese al sitio para aprobarlo.', 'From: info@tahitiparadise.com');
                } else {
                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->warningMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                }
            } catch (Zend_Exception $exc) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exc->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }


            //Manejo avisos y redirecciono
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => 'Cuenta creada con éxito. La misma será auditada por un administrador antes de que pueda usarla.'));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }
    }
    public function testAction() {
        $this->view->action = 'account';
        
        // exit();
        /* EMAIL */
        Zend_Layout::startMvc(array('layout' => 'email', 'layoutPath' => '../application/layouts/scripts/'));
        $layout = $this->_helper->layout->getLayoutInstance();
        $this->view->post=$_POST;
        $this->view->id=$id;
        $layout->content = $this->view->render('emails/test.phtml');
        $html =  $layout->render();
        $this->_helper->Mail->sendEmail($html, "Creacion de cuentas","mdampuero@gmail.com");
        exit("ok");
        mail('info@tahitiparadise.com', 'Tahiti Paradise - Nuevo usuario', 'Se creo un nuevo usuario. Ingrese al sitio para aprobarlo.', 'From: info@tahitiparadise.com');
                
    }

}
