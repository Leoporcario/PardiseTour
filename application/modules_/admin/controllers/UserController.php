<?php

require_once 'Users.php';
require_once 'Common.php';

class Admin_UserController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'IDUser', 'label' => 'N° Usuario', 'list' => true, 'width' => 100, 'class' => 'id', 'order' => true),
        array('field' => 'UName', 'label' => 'Nombre', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'ULastname', 'label' => 'Apellido', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'ULoginname', 'label' => 'Nombre de Usuario', 'required' => true, 'list' => true, 'search' => true, 'order' => true, 'class' => 'text-info'),
        array('field' => 'UEmail', 'label' => 'Email', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'UPassword', 'label' => 'Contraseña', 'required' => true, 'list' => false, 'order' => true),
        array('field' => 'UPassword_rectify', 'label' => 'Repetir contraseña', 'required' => true, 'list' => false, 'order' => true)
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo usuario', 'icon' => 'plus', 'controller' => 'user',  'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los usuarios', 'icon' => 'list', 'controller' => 'user',  'action' => 'index'),
    );
    var $options = array(
        array('type' => 'link', 'title' => 'Editar', 'icon' => 'edit text-primary', 'controller' => 'user', 'action' => 'edit'),
        array('type' => 'link', 'title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'controller' => 'user', 'action' => 'delete'),
    );
    
    var $users;

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
        $this->view->menuCurrent = 'user';
        $this->view->currentIcon = 'glyphicon glyphicon-user';
        $this->view->currentBrand = 'Usuarios';

        //Messages (error and success)
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Usuario guardado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Usuario editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Usuario eliminado con éxito!';
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

        //Some extra scripts        
        
        //Models
        $this->users = new Model_DBTable_Users();
        $this->form = new Zend_Form();
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
            $results_all = $this->users->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
            unset($_POST['password2']);

            //Llamo al modelo y guardo los datos
            try {
                $id = $this->users->add($_POST);
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
        $this->view->formId = 'userForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'plus';
        $this->view->title = 'Agregar nuevo usuario';
        $this->view->description = 'Complete el formulario para agregar un nuevo administrador del sistema';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function viewAction() {
        $this->view->h2 = 'Área N° ' . $this->parameters["id"];
        $this->view->result = $this->users->get($this->parameters["id"]);
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            //Elimino submit y otros no utiles
            unset($_POST['submit']);
            
            //Si las contraseñas no son iguales
            if($_POST['UPassword'] != $_POST['password2']){                
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Atención: Las contraseñas ingresadas no son iguales'));
                $this->_redirector->gotoSimple('edit', null, null, array('id' => $this->parameters['id'],'status' => 'warning'));
            }
            
            //Si no viene contraseña
            if($_POST['UPassword'] == ''){
                unset($_POST['UPassword']);                
            }
            unset($_POST['password2']);
            
            //Guardo y listo!
            try {
                $this->users->edit($_POST, $this->parameters["id"]);
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
        $this->view->formId = 'userForm';
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar usuario';
        $this->view->description = 'Vea y modifique la informacion del administrador del sistema seleccionado. Complete la contraseña solo si necesita cambiarla';
        $this->view->result = $this->users->get($this->parameters["id"]);
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->view->result = $this->users->delete_row($id);
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
            $this->view->result = $this->users->get($id);
            $this->view->h2 = "Borrar usuario";
        } else {
            $this->_redirect('admin/user/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar usuario';
        $this->view->description = 'Seleccione una opción';
    }

}
