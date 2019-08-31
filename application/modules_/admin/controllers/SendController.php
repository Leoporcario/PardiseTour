<?php

require_once '../application/models/DbTable/Newsletters.php';

require_once '../application/models/DbTable/Sends.php';

require_once '../application/models/DbTable/SendDetail.php';

require_once '../application/models/DbTable/Suscriptors.php';

require_once '../application/models/DbTable/Categories.php';

require_once '../application/models/DbTable/SuscriptorCategory.php';

require_once 'Common.php';

class Admin_SendController extends Zend_Controller_Action {

    var $response;
    var $model;
    var $model_send_detail;
    var $model_newsletter;
    var $model_suscriptor;
    var $model_category;
    var $model_new;
    var $model_functions;

    public function init() {
        //USER SESSION
        $options = array(
            'layout' => 'admin',
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);
        //LAYOUTS

        $this->response = $this->getResponse();
        $this->view->subMenuCurrent = $this->_getParam('controller') . "_" . $this->_getParam('action');
        $this->view->menuCurrent = 'newsletter';
        $this->view->controller = $this->_getParam('controller');
        $this->view->action = $this->_getParam('action');

        $this->response->insert('menu', $this->view->render('/menu.phtml'));
        $this->response->insert('subMenu', $this->view->render('/newsletter/menu.phtml'));
        //MODELS

        $this->model = new Model_DBTable_Sends();

        $this->model_send_detail = new Model_DBTable_SendDetail();

        $this->model_newsletter = new Model_DBTable_Newsletters();

        $this->model_suscriptor = new Model_DBTable_Suscriptors();
        
        $this->model_category = new Model_DBTable_Categories();
        
        $this->model_suscriptor_category = new Model_DBTable_SuscriptorCategory();

        $this->model_new = new Model_DBTable_News();

        $this->form = new Zend_Form();
    }

    public function indexAction() {

        $this->view->h2 = "Envíos anteriores";

        $results = $this->model->showAll();

        $this->view->envios = $results;
    }

    public function addAction() {
        $idnl = $this->_getParam('idnl', 0);

        if ($idnl != 0) {
            $this->view->idnl = $idnl;
        }

        if ($this->getRequest()->isPost()) {

            if ($this->form->isValid($this->getRequest()->getPost())) {
                
                if(count($_POST['IDCategory']) > 0){
                    $idSuscriptoresFinal = array();
                    foreach($_POST['IDCategory'] as $idCategoria){
                        $idSuscriptores = $this->model_suscriptor_category->showAllByCategory($idCategoria);
                        foreach($idSuscriptores as $id){
                            array_push($idSuscriptoresFinal, $id);
                        }
                    }
                }

                $date = date("Y-m-d");

                $time = date("H:i:s");

                $IDNewsletter = $_POST['IDNewsletter'];

                $IDEnvio = $this->model->add(array('IDNewsletter' => $IDNewsletter, 'EFecha' => $date, 'EHora' => $time, 'EEstado' => 1));

                foreach ($_POST['IDSuscriptor'] as $IDSuscriptor) {
                    array_push($idSuscriptoresFinal, $IDSuscriptor);
                }
                
                $idSuscriptoresFinal = array_unique($idSuscriptoresFinal);
                
                foreach($idSuscriptoresFinal as $IDSuscriptor){                    
                    $this->model_send_detail->add(array('IDSuscriptor' => $IDSuscriptor, 'IDEnvio' => $IDEnvio, 'EDEstado' => 0));
                }

                $this->_redirect('admin/send/view/id/' . $IDEnvio);
            }
        }

        $this->view->h2 = "Enviar newsletter a suscriptores";

        $newsletters = $this->model_newsletter->showAll();

        $this->view->newsletters = $newsletters;

        $suscriptors = $this->model_suscriptor->showAll();

        $this->view->suscriptors = $suscriptors;
        
        $categorias = $this->model_category->showAll();

        $this->view->categorias = $categorias;
    }

    public function editAction() {
        $this->response->insert('menuLogin', $this->view->render('/menuLogin.phtml'));
        $this->response->insert('titleMenu', 'Administrador de Noticias');
        $this->response->insert('menu', $this->view->render('/menu.phtml'));
        $this->response->insert('subMenu', $this->view->render('/newsletter/menu.phtml'));

        $id = $this->_getParam('id', 0);

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {

                $this->model->edit($_POST);
                $this->view->message = 'Newsletter modificado exitosamente.';
            }
        }
        $id = $this->_getParam('id', 0);

        if ($id > 0) {

            $this->view->result = $this->model->get($id);

            $this->view->news = $this->model_new->showAll();

            $this->view->newsletter_new = $this->model_newsletter_new->showAll($id);

            $this->view->types = $this->model_type->showAll();

            $this->view->headers = $this->model_header->showAll();

            $this->view->footers = $this->model_footer->showAll();

            $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Form.js");

            $this->response->insert('titleSubMenu', 'Editar Newsletter');
        } else {
            $this->_redirect('admin/newsletter/');
        }
    }

    public function viewAction() {
        $this->view->headScript()->appendFile($this->view->baseUrl() . "/js/Common.js");

        $id = $this->_getParam('id', 0);

        if ($id > 0) {

            $this->view->IDEnvio = $id;

            $send = $this->model->get($id);

            $this->view->resumen = "Resumen del envio a realizar";

            $newsletter = $this->model_newsletter->get($send['IDNewsletter']);

            $this->view->newsletter = $newsletter;

            $suscriptores = $this->model_send_detail->getSuscriptor($id);

            $this->view->array_suscriptor = $suscriptores;

            $this->view->array_suscriptors = $array_suscriptor;
        } else {
            $this->_redirect('admin/send/');
        }
    }

    public function deleteAction() {
        $id = $this->_getParam('id', 0);

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid($this->getRequest()->getPost())) {
                $delete_request = $this->getRequest()->getPost('delete_request');

                if ($delete_request == "Si") {

                    $detalles_envio = $this->model_send_detail->delete_row_by_envio($IDEnvio);

                    $this->view->result = $this->model->delete_row($id);

                    $this->_redirect('admin/send/');
                } else if ($delete_request == "No") {

                    $this->_redirect('admin/send/');
                }
            }
        }
        if ($id > 0) {
            $this->view->result = $this->model->get($id);
            $this->response->insert('titleSubMenu', 'Eliminar Newsletter');
        } else {
            $this->_redirect('admin/newsletter/');
        }
    }

    public function popupAction() {

        $this->_helper->layout->disableLayout();

        $this->view->message = 'Envio de newsletter:';
    }

}

