<?php
class FilmsController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$this->_forward('liste'); // tous les templates s'appellent index c'est fatiguant...
    }


    public function listeAction(){

    	// n'est appelé qu'en AJAX...
    	$this->_helper->layout->disableLayout();

        $film = new Default_Model_FilmMapper();
        $films = $film->fetchAll();
        //$films = $film->simulateFetchAll();

//        CFDJ_Debug::vd($films);

        $paginator = Zend_Paginator::factory($films);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        //$paginator->setView($view);
        $paginator->setItemCountPerPage(2);
        $this->view->paginator = $paginator;
        //$this->view->firstLoad = ! $this->_hasParam('page');
    }
}
