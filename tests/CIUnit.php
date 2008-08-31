<?php

/*
* fooStack, CIUnit
* Copyright (c) 2008 Clemens Gruenberger
* Released with permission from www.redesignme.com, thanks guys!
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

error_reporting(E_STRICT);

//load Testing
require_once 'PHPUnit/Framework.php';
require_once 'CIUnitTestCase.php';
require_once 'CIUnitTestSuite.php';
define('CIUnit_Version', '0.10');

//start CodeIgniter
$rel_dir = dirname(__FILE__).'/../../../';
include_once $rel_dir.'index.php';

define('TESTSPATH', APPPATH.'tests/');

/**
* CIUnit Class
* guides CI to behave nicely during tests..
*
* during the tests you can use:
*
* in setUp function:
* $this->CI = set_controller('controller_to_test');
* to set a different CI controller active
*
* use your controller functions simply like:
* $this->CI->function();
*
* created browser output is accessible like so
* $output = output();
* this function yields only once and is then reset to an empty string
*
* template vars like so:
* $vars = viewvars();
* they are also reset after this call
*/
class CIUnit{

    private static $instance;

    public static $controller;
    public static $current;
    public static $controllers = array();

    public static $spyc;
    public static $fixture;

    public function __construct(){
        self::$instance = &$this;
    }

    public function &get_CIU(){
        return self::$instance;
    }

    public static function &set_controller($controller='Controller'){
        $controller_name = array_pop(split('/', $controller));
        //echo "\nc name ".$controller_name;
        //is it the current controller?
        if($controller_name == self::$current){
            //we have nothing to do, return current controller
            //echo "current found!";
            output(); viewvars();
            return self::$controller;
        }

        //the current controller must be archieved before littered
        $loader =& load_class('Loader');
        self::$controllers[self::$current] = array(
            'adress' => self::$controller,
            'models' => $loader->_ci_models,  //this might be an update if it was there before
           // FIXME, all additional properties of the loader / controllers
          // that have to be reset must go in some test config file..
            //'components' => $loader->_ci_components,
            //'classes' => $loader->_ci_classes
            );
        //clean up the current controllers mess
        //reset models
        $loader->_ci_models = array();
        //reset components
        //$loader->_ci_components = array();
        //reset saved queries
        self::$controller->db->queries = array();
        //clean output / viewvars as well;
        if(isset(self::$controller->output)){
            output(); viewvars();
        }
        //the requested controller was loaded before?
        if(isset(self::$controllers[$controller_name])){
            //echo "saved found!";
            //load it
            $old = self::$controllers[$controller_name];
            self::$controller = $old['adress'];
            self::$current = $controller_name;
            $loader->_ci_models = $old['models'];
            //$loader->_ci_components = $old['components'];
            //$loader->_ci_classes = $old['classes'];
            CI_Base::$instance = self::$controller; //so get_instance() provides the correct controller
        }else{
            //echo "load new";
            //it was not loaded before
            if(!class_exists($controller_name)){
                include_once(APPPATH.'controllers/'.$controller.EXT);
            }
            self::$controller = new $controller_name();
            self::$current = $controller_name;
        }
        return self::$controller;
    }


    public static function &get_controller(){
        return self::$controller;
    }

    /**
    * get filenames eg for running test suites
    */
    public static function files($pattern, $path=".", $addpath=FALSE){ // lists all pdf files as links
        if (strpos($path, '/') === FALSE){
          if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE){
            $system_folder = realpath(dirname(__FILE__)).'/'.$path;
          }
        }
        else{
          // Swap directory separators to Unix style for consistency
          $path = str_replace("\\", "/", $system_folder);
        }
        if(substr($path,-1)!="/"){$path.="/";}
        $dir_handle = @opendir($path) or die("Unable to open $path");
        $outarr=array();

        while (false !== ($file = readdir($dir_handle)) ){
          if (preg_match($pattern, $file)){
            if($addpath){$file=$path.$file;}
            $outarr[]=$file;
          }
        }
        //could also use preg_grep!
        closedir($dir_handle);
        return $outarr;
    }
}

//=== convenience functions ===
// instead of referring to CIUnit directly

/**
* retrieves current CIUnit Class Singleton
*/
function &get_CIU(){
    return CIUnit::get_CIU();
}

/**
* sets CI controller
*/
function &set_controller($controller='Controller')
{
	return CIUnit::set_controller($controller);
}

/**
* retrieves current CI controller from CIUnit
*/
function &get_controller()
{
  return CIUnit::get_controller();
}

/**
* retrieves the cached output from the output class
* and resets it
*/
function output(){
  return CIUnit::$controller->output->pop_output();
}

/**
* retrieves the cached template vars from the loader class (stored here for assignment to views)
* and resets them
*/
function viewvars(){
  if(isset(CIUnit::$controller->load->_ci_cached_vars)){
    $out = CIUnit::$controller->load->_ci_cached_vars;
    CIUnit::$controller->load->_ci_cached_vars = array();
    return $out;
  }
  return array();
}

//=== and off we go ===
$CI = set_controller();
$CI->load->library('Spyc');
$CI->load->library('Fixture');
CIUnit::$spyc = &$CI->spyc;
CIUnit::$fixture = &$CI->fixture;