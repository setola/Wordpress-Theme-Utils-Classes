<?php

namespace WPTU\Core\Helpers;
use WPTU\Core\Singleton;
use WPTU\Core\Helpers\HtmlHelper;

/**
 * Class TestManager
 * Manages the addition of information for remote javascript testing.
 * For example with zombiejs and node.
 * @version 1.0.0
 */
class RemoteJavascriptTests extends Singleton{
    private $variable_name = 'window.tests';
    private $user_agent = '';

    /**
     * @var array stores the key\value pair for the current tests set
     */
    private $tests;

    /**
     * Initializes a new object
     */
    protected function __construct(){
        add_action('wp_footer', array(&$this, 'on_print_footer_script'), 9999);
    }

    /**
     * Sets the user agent Javascript data will be showed to.
     * The JS data will be shown only if the client user agent contains the given sctring
     * @param string $user_agent the user agent string
     * @return $this RemoteJavascriptTests for chainability
     */
    public function set_user_agent_contains($user_agent) {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * Sets the name of the javascript variable where tests data is stored
     * @param string $variable_name the javascript variable name
     * @return RemoteJavascriptTests $this for chainability
     */
    public function set_variable_name($variable_name) {
        $this->variable_name = $variable_name;

        return $this;
    }

    /**
     * Adds a test with given key and value
     * @param $key string the key
     * @param $value mixed the value
     * @return $this for chainability
     */
    public function add_parameter($key, $value){
        $this->tests[$key] = $value;
        return $this;
    }

    /**
     * Adds tests always useful: page generation time, number of queries and requested url
     * @return $this for chainability
     */
    public function add_defaults(){
        add_action('wp_footer', array(&$this, 'add_page_generation_time'), 9998, 0);
        add_action('wp_footer', array(&$this, 'add_number_of_queries'), 9998, 0);
        add_action('wp_footer', array(&$this, 'add_requested_uri'), 9998, 0);
        return $this;
    }

    /**
     * Adds the page generation time to the current set of tests
     * Must be hooked to the end of process (for example wp_footer)
     * @param string $key the key, default pageGenerationTime
     * @return $this for chainability
     */
    public function add_page_generation_time($key='pageGenerationTime'){
        global $timestart, $timeend;
        $timeend = microtime( true );
        $timetotal = $timeend - $timestart;
        $this->add_parameter($key, $timetotal);
        return $this;
    }

    /**
     * Adds the number of queries used to generate the current page.
     * Must be hooked to the end of process (for example wp_footer)
     * @param string $key the key, default numberOfQueries
     * @return $this for chainability
     */
    public function add_number_of_queries($key='numberOfQueries'){
        global $wpdb;
        $this->add_parameter($key, $wpdb->num_queries);
        return $this;
    }

    /**
     * Add the requested url to the tests set
     * @param string $key the key, default requestedUrl
     * @return $this for chainability
     */
    public function add_requested_uri($key='requestedUrl'){
        $this->add_parameter($key, 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        return $this;
    }

    /**
     * Callback for wp_footer, prints the window.tests variable
     * Useful to read the information on a external node.js environment (ex zombiejs)
     */
    public function on_print_footer_script(){
        if(empty($this->user_agent) || strpos($_SERVER['HTTP_USER_AGENT'], $this->user_agent) !== false)
            echo HtmlHelper::script($this->variable_name. ' = ' . json_encode($this->tests) . ';');
    }
}