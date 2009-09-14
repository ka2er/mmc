<?php
class Default_Model_FilmMapper
{
	protected $_path;
	protected $_extensions; // comma separated


	public function __construct(){
    	$c = Zend_Registry::get('config');

    	$this->_path = $c->film->path;
    	$this->_extensions = $c->film->extensions;
	}


    /**
     * retourne la liste des films trouvés
     * @return array
     */
    public function fetchAll()
    {
    	$pattern = $this->_path . DIRECTORY_SEPARATOR .'*.{'.$this->_extensions.'}';
    	$t = array();
    	foreach(glob(sql_regcase($pattern), GLOB_BRACE) as $file) { // sql_regcase pour matcher avi et AVI
    		$t[] = new Default_Model_Film($file);
    	}
    	return $t;
    }

    /**
     * simulation de la liste des films trouvés
     * @return array
     */
    public function simulateFetchAll()
    {
    	$t_fake = array(
    		'b13 ultimatum-SCR.avi',
    		'b13 ultimatum-SCR.avi',
    		'l\'arme fatale 4.avi',
			'fantastic 4.avi',
    	);

    	$t = array();
    	foreach($t_fake as $file) { // sql_regcase pour matcher avi et AVI
    		$t[] = new Default_Model_Film($file);
    	}
    	return $t;
    }


    /**
     *
     * @param $file
     * @return unknown_type
     */
    public function get($file) {

    	if(file_exists($this->_path . DIRECTORY_SEPARATOR . $file))
    		return $this->_path . DIRECTORY_SEPARATOR . $file;

    	throw new Exception("Unable to found Film $file");
    }
}
