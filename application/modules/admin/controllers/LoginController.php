<?php

class Admin_LoginController extends Zend_Controller_Action
{
	
	var $form;
	var $response;

    public function init()
    {
		$this->form = new Zend_Form();
		$this->_helper->layout->disableLayout();	
                $this->_redirector = $this->_helper->getHelper('Redirector');	
                $this->messages = $this->_helper->flashMessenger->getMessages();
    }
    	
	public function indexAction()
    {
		
		if($this->getRequest()->isPost()){
			try{
			$formData = $this->getRequest()->getPost();
                        if($this->getRequest()->getPost('ULoginname') == '' || $this->getRequest()->getPost('UPassword') == ''){
                            $response = "Login incorrecto!";
                        }else{
			
                            if($this->form->isValid($formData)){

                                    $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
                                    $authAdapter ->setTableName('cms_user')
                                                             ->setIdentityColumn('ULoginname')
                                                             ->setCredentialColumn('UPassword')
                                                             ->setCredentialTreatment('UName')
                                                             ->setIdentity($_POST['ULoginname'])
                                                             ->setCredential($_POST['UPassword']);  

                                    $auth = Zend_Auth::getInstance();
                                    $result = $auth->authenticate($authAdapter);

                                    if ($result->isValid()){

                                            $data = $authAdapter->getResultRowObject(array('IDUser','ULoginname','UPassword', 'UName', 'ULastname'));

                                            $auth->getStorage()->write($data);
                                            $this->_helper->flashMessenger->addMessage(array('type' => 'success', 'message' => 'Login exitoso!'));
                                            $this->_redirector->gotoSimple('index', 'index', 'admin', array('status' => 'success'));
                                    }else{
                                            $response = "Login incorrecto!";
                                            $this->view->statusBar = 'danger';
                                    }
                            }
                        }
                    }catch(Zend_Exception $exc){
                        echo $exc->getMessage();
                        exit();
                    }
		}
        $this->view->response = $response;

    }
	public function logoutAction()
    {
        $storage = new Zend_Auth_Storage_Session();
        $storage->clear();
        $this->_redirect('/admin');
    }

}