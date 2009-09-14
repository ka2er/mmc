<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
   	protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default',
            'basePath'  => dirname(__FILE__),
        ));
        return $autoloader;
    }

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initConfig() {
    	Zend_Registry::set('config', new Zend_Config($this->getOptions()));
    }

    protected function _initAutoloader(){
    	$autoloader = Zend_Loader_Autoloader::getInstance();
    	$autoloader->registerNamespace('CFDJ');
    }

    protected function _initPaginator(){
    	Zend_Paginator::setDefaultScrollingStyle('Sliding');
    	Zend_View_Helper_PaginationControl::setDefaultViewPartial(
    		'pagination_control.phtml'
    	);
    	//$paginator->setView($view);
    }

    protected function _initJquery(){
    	ZendX_JQuery::enableView($this->getResource('view'));
    }

    protected function _initProxy(){
    	if($this->getEnvironment() == 'fdj') {
    		$config = Zend_Registry::get('config');

    		$proxy = $config->proxy;

    		$http_option = array(
			    'adapter'    => 'Zend_Http_Client_Adapter_Proxy',
			    'proxy_host' => $proxy->host,
			    'proxy_port' => $proxy->port,
			   // 'proxy_user' => $proxy->user,
			    //'proxy_pass' => $proxy->pass
			);
			Zend_Registry::set('http_client_config', $http_option);
    	}
    }
}

