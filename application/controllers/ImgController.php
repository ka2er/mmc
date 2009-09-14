<?php
class ImgController extends Zend_Controller_Action
{
    public function thumbAction()
    {

        $this->_helper->layout->disableLayout();

        try {
	        $film_mapper = new Default_Model_FilmMapper();
	        $film = new Default_Model_Film($film_mapper->get($this->_getParam('file')));
        } catch (Exception $e) {
        	throw new Zend_Controller_Action_Exception($e->getMessage(), 404);
        }
	    $this->view->img = $film->getThumb(150, 100);
    }
}
