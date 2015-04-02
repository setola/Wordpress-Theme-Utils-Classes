<?php

namespace WPTU\Core\Autoloader;

/**
 * Contains the ClassAutoloader class definitions
 */
use WPTU\Core\Singleton;

    /**
 * Autoload needed classes
 * @author etessore
 * @version 1.0.2
 * @package classes
 */
 
/* 
 * Changelog:
 * 1.0.2
 * 	rewrited as singleton
 * 1.0.1
 * 	support multiple search path to allow children theme to extend classes
 * 1.0.0
 * 	Initial release
 */
class ClassAutoloader extends Singleton {
	
	/**
	 * @var array stores the path the system will scan for files
	 */
	public $loading_template;
	
	/**
	 * Initializes the autoloader
	 */
	protected function __construct() {
		$this->registerAutoload();
	}
	
	/**
	 * Set the loading template
	 * @param string $tpl the template
	 * @return ClassAutoloader $this for chainability
	 */
	public function addLoadingTemplate($tpl){
		$this->loading_template[] = $tpl;
		return $this;
	}
	
	/**
	 * Register the autoload function to PHP
	 */
	public function registerAutoload(){
		spl_autoload_register(array($this, 'loader'));
	}
	
	/**
 	 * Autoload needed classes
	 * @param String $className the name of the class
	 * @return ClassAutoloader $this for chainability
	 */
	private function loader($className) {
        $fileRelativePath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

		foreach($this->loading_template as $tpl){
			$filename = str_replace(
				array('%classname%'), 
				array($fileRelativePath),
				$tpl
			);
			if(file_exists($filename)) {
				include_once $filename;
				return $this;
			}
		}



		return $this;
	}
}
