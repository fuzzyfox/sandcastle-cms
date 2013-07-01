<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
	
	class CMS extends CI_Controller {
		private $markdown;

		public function __construct(){
			parent::__construct();
			// load the spark
			$this->load->spark('sandcastle-cms/0.1.0');

			// load markdown library
			$this->markdown =& $this->cms_lib->get_markdown_instance();

			// load file helper
			$this->load->helper('file');
		}

		public function _render($file){
			$file = rtrim($file, '/');
			// grab the contents of the file requested
			$markdown = read_file("./content/$file.md");
			
			// check file exists/readable
			if(!$markdown){
				return show_404($file);
			}

			$this->cms_lib->render($markdown, ($this->uri->uri_string() == $this->config->base_url()));
		}

		public function _remap($method, $file = array()){
			$file = implode('/', $file);
			if(method_exists($this, $method)){
				return $this->$method($file);
			}

			return $this->_render($method.'/'.$file);
		}
	}