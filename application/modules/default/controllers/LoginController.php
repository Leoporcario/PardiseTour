<?php

class LoginController extends Zend_Controller_Action {

    var $form;
    var $response;

    public function init() {
        $this->form = new Zend_Form();
        $this->_helper->layout->disableLayout();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->messages = $this->_helper->flashMessenger->getMessages();
    }

    public function indexAction() {

        if ($this->getRequest()->isPost()) {
            try {
                $formData = $this->getRequest()->getPost();
                if ($this->getRequest()->getPost('SEmail') == '' || $this->getRequest()->getPost('SPassword') == '') {
                    $response = "Login incorrecto!";
                } else {

                    if ($this->form->isValid($formData)) {

                        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
                        $authAdapter->setTableName('cms_suscriptor')
                                ->setIdentityColumn('SEmail')
                                ->setCredentialColumn('SPassword')
                                ->setCredentialTreatment('SNombre')
                                ->setIdentity($_POST['SEmail'])
                                ->setCredential($_POST['SPassword']);

                        $auth = Zend_Auth::getInstance();
                        $result = $auth->authenticate($authAdapter);

                        if ($result->isValid()) {

                            $data = $authAdapter->getResultRowObject(array('IDSuscriptor', 'SNombre', 'SEmail', 'SPassword'));

                            $auth->getStorage()->write($data);
                            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => 'Login exitoso!'));
                            $this->_redirector->gotoSimple('index', 'index', 'default', array('status' => 'success'));
                        } else {
                            $response = "Login incorrecto!";
                            $this->view->statusBar = 'danger';
                        }
                    }
                }
            } catch (Zend_Exception $exc) {
                echo $exc->getMessage();
                exit();
            }
        }
        $this->view->response = $response;
    }

    public function logoutAction() {
        $storage = new Zend_Auth_Storage_Session("publicUser");
        $storage->clear();
        $this->_redirect('/admin');
    }

}
