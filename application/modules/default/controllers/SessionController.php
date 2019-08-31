<?php

require_once 'Islas.php';

/* TODO: Bring limits from BD */

class SessionController extends Zend_Controller_Action {

    var $form;
    var $response;
    var $islas;

    public function init() {
        $this->form = new Zend_Form();
        $this->_helper->layout->disableLayout();

        $this->islas = new Model_DBTable_Islas();
    }

    public function preDispatch() {
        $this->session = new Zend_Session_Namespace('publicUser');
    
        //Define un lenguaje default
        if (!$this->session->lang) {
            $this->session->lang = "En";
        }
    }

    public function inputAction() {
        if ($this->_request->isXmlHttpRequest()) {
            if ($this->session->inputsRendered < $this->session->inputLimit || $this->session->inputLimit == 0) {
                $this->view->enable = true;
                $this->view->inputName = $this->_getParam('inputName', 'images[]');
                $this->view->inputType = $this->_getParam('inputType', 'text');
                $this->view->inputLabel = $this->_getParam('inputLabel', ' ');
                $this->session->inputsRendered++;
            } else {
                $this->view->enable = false;
            }
        }
    }

}
