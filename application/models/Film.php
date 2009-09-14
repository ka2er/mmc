<?php
class Default_Model_Film
{
	/**
	 * nom du fichier
	 *
	 * @var string
	 */
    protected $_file;

    protected $_ffmpeg;

    static protected $_id3;
    protected $_t_id3;

    public function __construct($file) {
    	$this->_file = $file;

    	// we need basename..

    	// props ...
    }

    /**
     * retourne une instance de ffmpeg_info
     *
     * @return ffmpeg_info
     */
    private function _info(){
    	if(! $this->_ffmpeg instanceof ffmpeg_movie) {
    		$this->_ffmpeg = new ffmpeg_movie($this->getPath());
    	}

    	return $this->_ffmpeg;
    }

    /**
     * retourne le tableau de l'analyse du film
     *
     * @return array
     */
    private function _id3(){
    	require_once('getid3/getid3.php');

    	if(! $this->_id3 instanceof getID3) {
    		$this->_id3 = new getID3;
    	}

    	if(!is_array($this->_t_id3)) {
    		$this->_t_id3 = $this->_id3->analyze($this->getPath());
    		getid3_lib::CopyTagsToComments($this->_t_id3);
    	}

    	return $this->_t_id3;
    }

    /**
     * magical getter proxied to ffmpeg_info
     *
     * @param $name
     * @return unknown_type
     */
    public function __get($name) {
    	if(strstr($name, 'info_') && method_exists('ffmpeg_movie', $method = 'get'.ucfirst(substr($name, 5)))) {
    		return $this->_info()->$method();
    	}
    }

    /**
     * retourne le nom du fichier complet avec son chemin
     *
     * @return string
     */
    public function getPath(){
    	return $this->_file;
    }


    /**
     * retourne le nom du fichier SANS chemin
     *
     * @return	string	$file
     */
    public function getFileName(){
    	return basename($this->_file);
    }


    /**
     * retourne un nom dans lequel tout ce qui peut sembler superflu est nettoyé
     *
     * @return string
     */
    public function getCleanedName(){

    	// decoupage chemin/fichier/extension...
    	$path_parts = pathinfo($this->getPath());

    	list($film_name, $extra) = explode('-', $path_parts['filename']);

    	// cut name on special keywords : on trouve le vrai nom du film ...
    	$c = Zend_Registry::get('config');
    	$t_sep = explode(',', $c->film->separators);
    	$garbage_idx = strlen($film_name) - 1;
    	foreach ($t_sep as $sep){
    		if($found = stripos($film_name, $sep)) {
    			$garbage_idx = min($found, $garbage_idx);
    		}
    	}
    	$film_name = substr($film_name, 0, $garbage_idx);

    	$film_name = str_replace(array('\'', '.', '-', '_'), ' ', $film_name);

    	return $film_name;
    }

    /**
     * retourne le nom proposé pour le fichier
     *
     * @return 	string	$name
     */
    public function getProposedName(){

    	// extension
    	$path_parts = pathinfo($this->getPath());
    	$ext = $path_parts['extension'];

    	// on recupere deja une version epuré du nom du fichier
    	$film_name = $this->getCleanedName();

    	// remise en forme du nom du film 'Mon.Film.A.Moi'
    	$film_name = strtolower($film_name);
    	$film_name = ucwords(trim($film_name));
    	$film_name = str_replace(' ', '.', $film_name);

    	// la langue
    	$lang = $this->getLanguages();
    	//$lang = $c->film->default_lang;
    	// si plus d'une langue c'est surement anglais + français
    	//if($this->_info()->hasAudio() && $this->info_audioChannels > 1)
    	//$lang = 'FR+EN';
    	//echo $this->info_audioChannels;

    	// la qualité
    	$quality = $this->getQuality();

    	// codec video
    	//$video_codec = $this->info_videoCodec; // method based on ffmpeg
    	$video_codec = $this->getVideoCodec();


    	// codec audio
    	//$audio_codec = $this->info_audioCodec == 'mp3' ? '' : '-'.$this->info_audioCodec; // based om ffmpeg
    	$audio_codec = $this->getAudioCodec();


    	$new_name = "$film_name-{$lang}.{$quality}.{$video_codec}{$audio_codec}.{$ext}";
    	return $new_name;
    }

    /**
     * renvoit une image type miniature
     *
     * @param $w	width
     * @param $h	height
     * @return true color GD Image
     */
    public function getThumb($w, $h){



    	/*
		for($i=0; $i < $max; $i++) {
    		$frame = $this->info_nextKeyFrame;
    	}
    	*/


    	// orginal size
    	$orig_w = $this->info_frameWidth;
    	$orig_h = $this->info_frameHeight;
    	$orig_rapport = $orig_w / $orig_h;

    	// taille de la frame resample (sans bandes noires)
    	$resampled_frame_h = $w / $orig_rapport;
    	$new_img_y_offset = ($h - $resampled_frame_h) / 2;

    	// une frame un peu au hasard...
    	$rand_frame_idx = rand(min(100, $this->info_frameCount-1), min(1000, $this->info_frameCount-1));
    	$frame = $this->_info()->getFrame($rand_frame_idx);

    	//$frame = $this->info_nextKeyFrame;

    	$img = $frame->toGDImage();

    	// une image pile poil à la taille de l'extrait
    	$resampled_frame = imagecreatetruecolor($w, $resampled_frame_h);
    	imagecopyresampled($resampled_frame, $img, 0, 0, 0, 0, $w, $resampled_frame_h, $orig_w, $orig_h);
    	imagedestroy($img);

    	// on met l'image croppé et resizé dans le thumb final
    	$new_img = imagecreatetruecolor($w, $h);
    	imagecopy($new_img, $resampled_frame, 0, $new_img_y_offset, 0, 0, $w, $resampled_frame_h);
    	imagedestroy($resampled_frame);
    	return $new_img;
    }

    /**
     * retourne le codec video
     * @return string
     */
    public function getVideoCodec(){
    	$t = $this->_id3();
    	if(isset($t['video']['fourcc']))
    		return $t['video']['fourcc'];

    	Zend_Debug::dump($t, "Unable to found video codec for ".$this->getFileName());
    	//throw new Exception("Fatal Error");
    }

    /**
     * retourne le codec video
     * @return string
     */
    public function getAudioCodec(){
    	$t = $this->_id3();

    	if(isset($t['audio']['dataformat'])) {
    		$codec = $t['audio']['dataformat'];

    		switch ($codec) {
    			case 'vorbis':
    				return '-OGG';

    			case 'mp3':
    				return '';

    			default:
    				return ".unknown($codec)";
    		}
    	}

       	Zend_Debug::dump($t, "Unable to found audio codec for ".$this->getFileName());
    	//throw new Exception("Fatal Error");
    }


    public function getLanguages(){
    	//$t = $this->_id3();
    	//CFDJ_Debug::vd($t);


    	// @todo: better

    	return 'FR';
    }

    /**
     * retourne la qualité supposée de la video en fonction de son nom s'il contient
     * SCREENER, de sa résolution horizontale, par défaut donne DVD ...
     *
     * @return string
     */
    public function getQuality(){

    	// 1 - is it screener ?
    	if(stripos($this->getFileName(), 'screener'))
    		return 'SCREENER';

    	// 2 - is it HD 720/1080
    	$t = $this->_id3();
    	if(isset($t['video']['resolution_x'])) {

    		$x_res = $t['video']['resolution_x'];

    		if($x_res >= 1080) return 'HD1080';
    		if($x_res >= 720) return 'HD720';

    	}

    	// fallback to DVD
    	return 'DVDRiP';
    }

    /**
     * utilisé lorsque echo est fait sur l'objet
     *
     * @return unknown_type
     */
    public function __toString() {
    	return $this->_file ." ($this->info_duration $this->info_frameWidth x $this->info_frameHeight $this->info_videoCodec $this->info_audioCodec)";
    }
}
