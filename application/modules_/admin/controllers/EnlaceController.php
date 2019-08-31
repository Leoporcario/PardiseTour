<?php

//Modelos
require_once 'EnlaceCategoria.php';
require_once 'Enlaces.php';
//Librerias
require_once 'PHPExcel/PHPExcel.php';
require_once 'Common.php';

class Admin_EnlaceController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'IDEnlace', 'label' => 'N° Enlace', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'EnombreEs', 'label' => 'Nombre', 'required' => false, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'Elink', 'label' => 'Link', 'required' => false, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'ECnombreEs', 'label' => 'Categoría', 'required' => false, 'list' => true, 'search' => true, 'order' => true),
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo enlace', 'icon' => 'link', 'controller' => 'enlace', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los enlaces', 'icon' => 'list', 'controller' => 'enlace', 'action' => 'index'),
        array('type' => 'divider'),
        array('type' => 'link', 'label' => 'Agregar nueva categoría de enlaces', 'icon' => 'tags', 'controller' => 'enlacecategoria', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todas las categoría de enlaces', 'icon' => 'list', 'controller' => 'enlacecategoria', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );    
    
    var $response;
    var $enlacesCategoria;
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
        $this->view->menuCurrent = 'enlace';
        $this->view->currentIcon = 'glyphicon glyphicon-link';
        $this->view->currentBrand = 'Enlaces';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Enlace creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Enlace editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Enlace eliminado con éxito!';
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
        $this->enlacesCategoria = new Model_DBTable_EnlaceCategoria();
        $this->enlaces = new Model_DBTable_Enlaces();
        $this->form = new Zend_Form();

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
            $results_all = $this->enlaces->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
        //Accion con POST
        if ($this->getRequest()->isPost()) {
            //Elimino submit y otros no utiles
            unset($_POST['submit']);
            //Llamo al modelo y guardo los datos
            try {                
                $id = $this->enlaces->add($_POST);                
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
        $this->view->formId = 'enlaceForm';
        //Datos de BD
        $this->view->categorias = $this->enlacesCategoria->showAll();

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nueva categoría (Enlaces)';
        $this->view->description = 'Complete el formulario para agregar un enlace hacia otro sitio';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }   

    public function editAction() {
        $enlace = $this->enlaces->get($this->view->parameters['id']);
        if ($this->getRequest()->isPost()) {
            //Deleting variables from post
            unset($_POST['submit']);
            try{                 
                $_POST['IDEnlace'] = $enlace['IDEnlace'];
                $this->enlaces->edit($_POST);
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
        $this->view->formId = 'enlaceForm';
        //Datos de BD
        $this->view->categorias = $this->enlacesCategoria->showAll();

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar Enlaces';
        $this->view->description = 'Complete el formulario para editar la información del enlaces';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
        $this->view->result = $enlace;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {                        
                        $this->view->result = $this->enlaces->delete_row($id);
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
            $this->view->result = $this->enlaces->get($id);
        } else {
            $this->_redirect('admin/enlace/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar Enlace';
        $this->view->description = 'Seleccione una opción - ';
    }

}

