<?php 
/**
 * Contains ForceDownload class definition
 */

namespace WPTU\Core\Helpers;
use WPTU\Core\Helpers\HtmlHelper;
use WPTU\Core\Singleton;

/**
 * Force an attachment to be downloaded
 * @author etessore
 * @version 1.0.0
 * @package WPTU\Core\Helpers
 */
class ForceDownload extends Singleton{
	
	/**
	 * Enables the force to download feature
	 * @param bool $priv
	 * @param bool $nopriv
	 */
	protected function __construct($priv=true, $nopriv=true){
		$this->hook();
	}

    /**
     * Hooks into WordPress Ajax system
     * @return $this ForceDownload for chainability
     */
    protected function hook(){
        add_action('wp_ajax_download', array(&$this, 'force_download'));
        add_action('wp_ajax_nopriv_download', array(&$this, 'force_download'));

        return $this;
    }

	/**
	 * AJAX callback for forcing the download of a file
	 */
	public function force_download(){
		if(!$this->check_id($_REQUEST['id'])){
            header('HTTP/1.0 401 Unauthorized');
			wp_die('Unauthorized Resource');
		}

		$file = get_attached_file($_REQUEST['id']);
		if(file_exists($file)){
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename= " . basename($file));
			header("Content-Transfer-Encoding: binary");
			die(file_get_contents($file));
		} else {
			header('HTTP/1.0 404 Not Found');
            die();
		}
	}

	/**
	 * Tests if the given id is a valid media library element
	 * Overload this to insert more checks
	 */
	public function check_id($id){
		if(!is_numeric($id)) return false;
        if(get_post_type($id) != 'attachment') return false;

		return true;
	}

	/**
	 * Calculates the url for the attachment with id $id
	 * @param int $id the attachment id
	 * @return string the download url
	 */
	public static function force_download_url($id){
		return admin_url('admin-ajax.php').'?action=download&id='.$id;
	}
}