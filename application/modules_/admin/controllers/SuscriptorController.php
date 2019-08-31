<?php

//Modelos
require_once 'Suscriptors.php';
require_once 'Categories.php';
require_once 'SuscriptorCategory.php';
//Librerias
require_once 'PHPExcel/PHPExcel.php';
require_once 'Common.php';

class Admin_SuscriptorController extends Zend_Controller_Action {

    var $fields = array(
        array('field' => 'IDSuscriptor', 'label' => 'N° Suscriptor', 'list' => true, 'width' => 120, 'class' => 'id', 'order' => true),
        array('field' => 'SNombre', 'label' => 'Nombre', 'required' => false, 'list' => true, 'search' => false, 'order' => false),
        array('field' => 'SEmail', 'label' => 'EMail', 'required' => true, 'list' => true, 'search' => true, 'order' => true),
        array('field' => 'SEstado', 'label' => 'Habilitado', 'required' => true, 'list' => true, 'search' => false, 'order' => true)
    );
    var $actions = array(
        array('type' => 'link', 'label' => 'Agregar nuevo suscriptor', 'icon' => 'user', 'controller' => 'suscriptor', 'action' => 'add'),
        array('type' => 'link', 'label' => 'Listar todos los suscriptores', 'icon' => 'list', 'controller' => 'suscriptor', 'action' => 'index'),
    );
    var $options = array(
        array('title' => 'Editar', 'icon' => 'edit text-primary', 'action' => 'edit'),
        array('title' => 'Eliminar', 'icon' => 'ban-circle text-danger', 'action' => 'delete'),
    );    
    
    var $response;
    var $suscriptor;
    var $category;
    var $suscriptorCategory;

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
        $this->view->menuCurrent = 'suscriptor';
        $this->view->currentIcon = 'glyphicon glyphicon-thumbs-up';
        $this->view->currentBrand = 'Suscriptores';

        //Messages (error and success)
        $this->warningMessage = 'Ya existe un suscriptor con el email ingresado.';
        $this->errorMessage = 'Ocurrio algun error en la ejecución de la transacción.';
        $this->newSuccessMessage = '<b>Información:</b> Suscriptor creado con éxito!';
        $this->editSuccessMessage = '<b>Información:</b> Suscriptor editado con éxito!';
        $this->deleteSuccessMessage = '<b>Información:</b> Suscriptor eliminado con éxito!';
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
        $this->suscriptor = new Model_DBTable_Suscriptors();
        $this->suscriptorCategory = new Model_DBTable_SuscriptorCategory();
        $this->category = new Model_DBTable_Categories();
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
            $results_all = $this->suscriptor->showAll($where, $this->parameters['sort'], $this->parameters['order']);
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
            //Si las contraseñas no son iguales
            if($_POST['UPassword'] != $_POST['password2']){                
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Atención: Las contraseñas ingresadas no son iguales'));
                $this->_redirector->gotoSimple('add', null, null, array('id' => $this->parameters['id'],'status' => 'warning'));
            }            
            //Elimino submit y otros no utiles
            unset($_POST['password2']);
            unset($_POST['submit']);
            if($_POST['UPassword']){
                $_POST['SPassword'] = $_POST['UPassword'];
                unset($_POST['UPassword']);                
            }
            //Llamo al modelo y guardo los datos
            try {
                if(count($this->suscriptor->showAll('SEmail = "' . $_POST['SEmail'] . '"')) < 1){
                    $id = $this->suscriptor->add($_POST);
                }else{
                    $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => $this->warningMessage));
                    $this->_redirector->gotoSimple('index', null, null, array('status' => 'warning'));
                }
            } catch (Zend_Exception $exc) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exc->getMessage()));
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
        $this->view->title = 'Agregar nuevo suscriptor';
        $this->view->description = 'Complete el formulario para agregar un nuevo suscriptor al sitio';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function masiveAction() {
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $id_categories = $_POST['IDCategory'];

                if ($_FILES["file"]['name']) {

                    $extension = end(explode('.', $_FILES["file"]['name']));
                    move_uploaded_file($_FILES["file"]["tmp_name"], "file_tmp." . $extension);
                    $fp = fopen("file_tmp." . $extension, "r");
                    $c = -1;
                    $c_error = 0;
                    if ($fp) {

                        while (!feof($fp)) {
                            $c++;
                            $line = fgets($fp, 1024);

                            $data = @explode(';', $line);
                            $mail = trim($data[0]);
                            if (count($data) > 1) {
                                $IDSuscriptor = $this->model->add(array('SEmail' => $mail));

                                if ($IDSuscriptor > 0) {
                                    foreach ($id_categories as $IDCategory) {
                                        $this->model_suscriptor_category->add(array('IDSuscriptor' => $IDSuscriptor, 'IDCategory' => $IDCategory));
                                    }
                                } else {
                                    $c_error++;
                                }
                            }
                        }
                        fclose($fp);
                        unlink("file_tmp." . $extension);
                        $c_ok = $c - $c_error;
                    }
                }
                $this->view->message = 'Se leyeron ' . $c . ' registros:<br>
                            ' . $c_ok . ' registros se cargaron exitosamente.<br>'
                        . $c_error . ' registros fallaron.<br>';
            }
        }

        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");
        $this->view->h2 = "Subir archivo con suscriptores";
        $this->view->categories = $this->model_category->showAll();
    }

    public function editAction() {
        $idSuscriptor = $this->_getParam('id',0);
        //Accion con POST
        if ($this->getRequest()->isPost()) {
            //Si las contraseñas no son iguales
            if($_POST['UPassword'] != $_POST['password2']){                
                $this->_helper->flashMessenger->addMessage(array('type' => 'warning', 'message' => 'Atención: Las contraseñas ingresadas no son iguales'));
                $this->_redirector->gotoSimple('edit', null, null, array('id' => $this->parameters['id'],'status' => 'warning'));
            }
            //Elimino submit y otros no utiles
            unset($_POST['submit']);
            if($_POST['UPassword']){
                $_POST['SPassword'] = $_POST['UPassword'];
            }
            unset($_POST['UPassword']);    
            unset($_POST['password2']);
            //Llamo al modelo y guardo los datos
            try {
                $_POST['IDSuscriptor'] = $idSuscriptor;
                $this->suscriptor->edit($_POST);
            } catch (Zend_Exception $exc) {
                //Redirecciono al index con error. Evita colgar la aplicación
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => $this->errorMessage . '<br>' . $exc->getMessage()));
                $this->_redirector->gotoSimple('index', null, null, array('status' => 'danger'));
            }

            //Manejo avisos y redirecciono
            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => $this->newSuccessMessage));
            $this->_redirector->gotoSimple('index', null, null, array('status' => 'success'));
        }
        if($idSuscriptor > 0){
            $this->view->result = $this->suscriptor->get($idSuscriptor);
        }else{
            $this->_redirect('/admin/suscriptor');
        }
        //idFormulario para validacion
        $this->view->formId = 'userForm';

        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'pencil';
        $this->view->title = 'Editar suscriptor';
        $this->view->description = 'Complete el formulario para modificar la información de un suscriptor al sitio. Complete los cambios contraseña y repetir contraseña para cambiarla.';
        
        //Envio mensajes nuevos a la vista
        $this->view->messages = $this->messages;
    }

    public function viewAction() {
        $id = $this->_getParam('id', 0);
        if ($id > 0) {

            $suscriptor = $this->model->getConPais($id);
            $suscriptor = $suscriptor[0];

            $comments = $this->model_comment->showAll('IDSuscriptor = ' . $suscriptor['IDSuscriptor']);

            $this->view->comments = $comments;

            $commentsimage = $this->model_commentimage->showAll('IDSuscriptor = ' . $suscriptor['IDSuscriptor']);

            $this->view->commentsimage = $commentsimage;

            $this->view->comments = $comments;

            $this->view->result = $suscriptor;

            $this->view->h2 = "Suscriptor " . $suscriptor['EMail'];
        } else {
            $this->_redirect('admin/suscriptor/');
        }
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);
        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Yes") {
                    try {
                        $this->view->result = $this->suscriptor->delete_row($id);
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
            $this->view->result = $this->suscriptor->get($id);
        } else {
            $this->_redirect('admin/suscriptor/');
        }
        
        //Icono, titulo y descripcion para el formulario
        $this->view->icon = 'ban-circle';
        $this->view->title = 'Eliminar suscriptor';
        $this->view->description = 'Seleccione una opción';
    }

}

