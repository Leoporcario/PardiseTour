<?php


class HelperController extends Zend_Controller_Action {

   
    
    public function init() {
       
        $this->response = $this->getResponse();
        $this->view->parameters = $this->_request->getParams();
        
    }

    public function changelngAction(){
        $this->_session = $this->_helper->getHelper('PublicUser');
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_session->setLang($this->view->parameters['lang']);
        echo '<pre>';
        print_r($this->view->parameters);
        echo '</pre>';
        exit();
    }

}
