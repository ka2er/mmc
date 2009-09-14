<?php

class ToolsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * cherche les films dont les noms ressemblent le plus...
     *
     * @todo: add cache (sqllite?)
     * @return unknown_type
     */
    public function similarAction()
    {
    	// retrieve film
    	$film = $this->_getParam('film');
    	$this->view->film = $film;

    	// list all files ...
        $film_mapper = new Default_Model_FilmMapper();
        $t = $film_mapper->fetchAll();

    	// compute levenshtein
    	$t_similar = array();
    	foreach ($t as $xfilm) {
    		if($xfilm->getFileName() == $film) continue; // don't compare with itself...

    		$t_similar[$xfilm->getFileName()] = levenshtein($film, $xfilm->getFileName());
    	}

    	// order them
    	asort($t_similar);

    	// return 5 first ...
    	$t = array_chunk($t_similar, 5, true);
    	$this->view->similar = $t[0];
    }


    public function searchAllocineAction()
    {
    	// retrieve film
    	$film = $this->_getParam('film');

    	// make allocine search...
    	$film = urlencode($film);
    	$this->view->proxy_buffer = file_get_contents("http://iphone.allocine.fr/recherche/default.html?motcle=$film&rub=1&page=1");;
    	$this->_forward('proxy');
    }

    public function proxyAction(){
    	$this->_helper->layout->setLayout('proxy');
    }

    /**
     * renomme un fichier
     *
     * l'url doit contenir les variables : path, film, new_name
     * @return json {status,command}
     */
    public function renameAction(){
    	$path = $this->_getParam('path', false);
    	$old_name = $this->_getParam('film', false);
    	$new_name = $this->_getParam('new_name', false);

    	if($path && $old_name && $new_name && file_exists($path.$old_name))
    		$ret = rename($path.$old_name, $path.$new_name);
    	else
    		$ret = false;

    	$this->_helper->json(array('status' => $ret, "command" => "mv {$path}$old_name {$path}$new_name"));
    }

    /**
     * effectue le deplacement du fichier dans un nouveau repertoire
     *
     * @return unknown_type
     */
    public function moveAction(){
    	$path = $this->_getParam('path', false);
    	$new_path = $this->_getParam('new_path', false);
    	$film = $this->_getParam('film', false);

    	if($path && $new_path && $film && file_exists($path.$film))
    		$ret = rename($path.$film, $new_path.$film);
    	else
    		$ret = false;

    	$this->_helper->json(array('status' => $ret, "command" => "mv {$path}$film {$new_path}$film"));

    }

    /**
     * affiche la page des liens de tests...
     * @return unknown_type
     */
    public function testAction(){
    	$this->_helper->layout->setLayout('blank');

    }
}

