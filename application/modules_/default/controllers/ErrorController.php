<?php

class ErrorController extends Zend_Controller_Action {

    public function init() {
        //Datos de login y sesion
        $params = $this->_getAllParams();
        $this->module = $params['module'];
        $options = array(
            'layout' => $this->module,
            'layoutPath' => '../application/layouts/scripts/'
        );
        Zend_Layout::startMvc($options);


        //Helpers
        $this->response = $this->getResponse();
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->messages = $this->_helper->flashMessenger->getMessages();
        $statusBar = $this->_getParam('status');
        $this->view->statusBar = $statusBar;
    }

    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        if (!$errors) {
            $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Ha llegado a la página de error'));
            $this->_redirector->gotoSimple('index', 'index', $this->module, array('status' => 'danger'));
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Error 404 - No existe la página buscada (No se encuentra controlador)'));
                $this->_redirector->gotoSimple('index', 'index', $this->module, array('status' => 'danger'));
                break;
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Error 404 - No existe la página buscada (No se encuentra acción)'));
                $this->_redirector->gotoSimple('index', 'index', $this->module, array('status' => 'danger'));
                break;
            default:
                $this->getResponse()->setHttpResponseCode(500);
                $this->_helper->flashMessenger->addMessage(array('type' => 'danger', 'message' => 'Error 500 - Ha ocurrido un error en la aplicación <br>' . $errors->exception));
                $this->_redirector->gotoSimple('index', 'index', $this->module, array('status' => 'danger'));
                break;
        }

        // Log exception, if logger available
        //if ($log == $this->getLog()) {
        //    $log->crit($this->view->message, $errors->exception);
        //}
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

}

?>
