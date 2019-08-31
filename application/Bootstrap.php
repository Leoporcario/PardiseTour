<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
	protected function _initAutoload(){
	
            /** Inicializa layout */
            $options = array(
                    'layout'	 => 'default',
                    'layoutPath' => '../application/layouts/scripts/'
            );
            Zend_Layout::startMvc($options);
            
	}
	
	protected function _initViewHelpers(){
            
		
	}
    
        protected function _initHelperPath()
        {
            Zend_Controller_Action_HelperBroker::addPath(
                    APPLICATION_PATH . '/modules/admin/controllers/helpers',
                    'Application_Controller_Action_Helper_');
        }
	
	
}

