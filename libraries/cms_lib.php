<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
	
	require_once(dirname(__FILE__) . '/../vendor/php-markdown/Michelf/Markdown.php');
	require_once(dirname(__FILE__) . '/../vendor/twig/lib/Twig/Autoloader.php');
	Twig_Autoloader::register();

	class CMS_lib {
		private $markdown;
		private $twig;
		private $CI;
		private $version;

		public function __construct(){
			// load ci instance
			$this->CI =& get_instance();

			// load markdown parser
			$this->markdown = new \Michelf\Markdown;

			// load twig
			$twig_loader = new Twig_Loader_String();
			$this->twig = new Twig_Environment($twig_loader, $this->CI->config->item('twig_config', 'sandcastle_cms'));
			$this->twig->addExtension(new Twig_Extension_Debug());

			// set cms version from config
			$this->version = $this->CI->config->item('version', 'sandcastle_cms');

			// ensure that cms installed
			if(!$this->is_installed()){
				$this->install();
				return;
			}

			// show warning if installed, but config does not reflect
			if($this->is_installed() !== $this->CI->config->item('is_installed', 'sandcastle_cms')){
				trigger_error("Warning: incorrect setting for sandcastle-cms may cause slow page loads.\n\nPlease set <code>\$config['sandcastle_cms']['is_installed'] = TRUE;</code>", E_USER_WARNING);
			}
		}

		/**
		 * Renders the provided markdown w/ theming
		 *
		 * A lot of the logic in the file comes from that in [Pico](http://pico.dev7studios.com/).
		 * 
		 * @param  string  $markdown      markdown to be rendered
		 * @param  boolean $is_front_page TRUE if current markdown is front page
		 * @return void
		 */
		public function render($markdown, $is_front_page = FALSE){
			// load the file helper
			$this->CI->load->helper('file');

			// some work on setting up pages objects
			$meta = $this->get_meta($markdown);
			$pages = $this->get_pages();
			$prev_page = array();
			$current_page = array();
			$next_page = array();
			while($current_page = current($pages)){
				if($meta['title'] == $current_page['title']){
					break;
				}
				next($pages);
			}
			$prev_page = next($pages);
			prev($pages);
			$next_page = prev($pages);
			// set initial params for twig to use with the render
			$twig_params = array(
				'config'		=> $this->CI->config->item('sandcastle_cms'),
				'base_dir'		=> BASEPATH.'../',
				'base_url'		=> $this->CI->config->base_url(),
				'theme_dir'		=> BASEPATH.'../themes/'.$this->CI->config->item('theme', 'sandcastle_cms').'/',
				'theme_url'		=> $this->CI->config->base_url().'themes/'.$this->CI->config->item('theme', 'sandcastle_cms').'',
				'site_title'	=> $this->CI->config->item('site_title', 'sandcastle_cms'),
				'meta'			=> $meta,
				'content'		=> $this->get_content($markdown),
				'pages'			=> $pages,
				'prev_page'		=> $prev_page,
				'current_page'	=> $current_page,
				'next_page'		=> $next_page,
				'is_front_page'	=> $is_front_page
			);

			echo $this->twig->render(read_file($twig_params['theme_dir'].'index.html'), $twig_params);
		}

		/**
		 * Parse the meta date out of a markdown file
		 *
		 * A lot of the logic in the file comes from that in [Pico](http://pico.dev7studios.com/).
		 * 
		 * @param  string $markdown content of the markdown file to parse
		 * @return assoc_array The meta data associated with the input file
		 */
		private function get_meta($markdown){
			$meta_item = array(
				'title'			=> 'Title',
				'description'	=> 'Description',
				'author'		=> 'Author',
				'date'			=> 'Date',
				'robots'		=> 'Robots'
			);

			foreach ($meta_item as $field => $regex) {
				if(preg_match('/^[ \t\/*#@]*'.preg_quote($regex, '/').':(.*)$/mi', $markdown, $match) && $match[1]){
					$meta_item[$field] = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $match[1]));
				}
				else {
					$meta_item[$field] = '';
				}
			}

			if($meta_item['date']) $meta_item['date_formated'] = date($this->CI->config->item('date_format', 'sandcastle_cms'), strtotime($meta_item['date']));

			return $meta_item;
		}

		/**
		 * Parse markdown content
		 *
		 * Parses markdown content to HTML and provides an alias to the site url `%base_url%`
		 *
		 * A lot of the logic in the file comes from that in [Pico](http://pico.dev7studios.com/).
		 * 
		 * @param  string $markdown The markdown to parse
		 * @return string           HTML output
		 */
		private function get_content($markdown){
			// remove comments and meta
			$content = preg_replace('#/\*.+?\*/#s', '', $markdown);
			// provide the %base_url% shorthand
			$content = str_replace('%base_url%', $this->CI->config->base_url(), $content);
			return $this->markdown->defaultTransform($content);
		}

		/**
		 * Creates page objects from an array of file paths
		 *
		 * A lot of the logic in the file comes from that in [Pico](http://pico.dev7studios.com/).
		 * 
		 * @return array An array of page objects.
		 */
		private function get_pages(){
			$this->CI->load->helper(array('file', 'text'));

			$pages = $this->get_files(BASEPATH.'../content/');
			$sorted_pages = array();
			$date_id = 0;
			foreach ($pages as $key => $page) {
				if(basename($page)	== '404.md'){
					unset($pages[$key]);
					continue;
				}

				$contents	= read_file($page);
				$meta		= $this->get_meta($contents);
				$contents	= $this->get_content($contents);
				$url		= str_replace(str_replace('system', 'content', BASEPATH), $this->CI->config->site_url().'/cms/', $page);
				$url		= str_replace('index.md', '', $url);
				$url		= str_replace('.md', '', $url);

				$data		= array(
					'title'			=> $meta['title'],
					'url'			=> $url,
					'author'		=> $meta['author'],
					'date'			=> $meta['date'],
					'date_formated'	=> date($this->CI->config->item('date_format', 'sandcastle_cms'), strtotime($meta['date'])),
					'content'		=> $contents,
					'excerpt'		=> word_limiter($contents, $this->CI->config->item('excerpt_length', 'sandcastle_cms'))
				);

				if($this->CI->config->item('pages_order_by', 'sandcastle_cms') == 'date'){
					$sorted_pages[$meta['date'].$date_id] = $data;
					$date_id++;
				}
				else {
					$sorted_pages[] = $data;
				}
			}

			if($this->CI->config->item('pages_order', 'sandcastle_cms') == 'desc'){
				krsort($sorted_pages);
			}
			else {
				ksort($sorted_pages);
			}

			return $sorted_pages;
		}

		/**
		 * Recursively get markdown files from dir
		 * @param  string $dir Directory path to start in
		 * @return array       An array of filepaths for markdown files.
		 */
		private function get_files($dir){
			$array_items = array();
			foreach (get_filenames($dir, TRUE) as $file) {
				if(is_dir($file)){
					$array_items = array_merge($array_items, $this->get_files($file));
				}
				else {
					if(pathinfo($file, PATHINFO_EXTENSION) === 'md'){
						$array_items[] = $file;
					}
				}
			}

			return $array_items;
		}

		/**
		 * Get the Markdown instance
		 * @return \Michelf\Markdown Markdown parser class
		 */
		public function get_markdown_instance(){
			return $this->markdown;
		}

		/**
		 * Check if sandcastle-cms is installed
		 * @return boolean Returns TRUE if installed.
		 */
		private function is_installed(){
			if($this->CI->config->item('is_installed', 'sandcastle_cms')){
				return TRUE;
			}

			// check controller is in place
			// check content dir exits and writable
			if(file_exists(APPPATH.'controllers/cms.php') &&
				is_dir(BASEPATH.'../content') &&
				is_writable(BASEPATH.'../content')){
				return TRUE;
			}

			return FALSE;
		}

		/**
		 * Install sandcastle-cms
		 * @return boolean Returns TRUE on success.
		 */
		private function install(){
			// get some folder perms
			$root_dir = substr(sprintf('%o', fileperms(BASEPATH.'../')), -4);
			$controller_dir = substr(sprintf('%o', fileperms(APPPATH.'controllers')), -4);

			// set some folder permissions
			@chmod(BASEPATH.'../', 0777);
			@chmod(APPPATH.'controllers/', 0777);

			// create content dir
			if((is_writable(BASEPATH.'../') &&
				mkdir(BASEPATH.'../content/')) || 
				is_dir(BASEPATH.'../content/')){
				// copy controller
				if(is_writable(APPPATH.'controllers/') &&
					copy('../controllers/cms.php', APPPATH.'controllers/cms.php')) {
					// double check install worked and change config file
					if($this->is_installed()){
						$this->CI->load->helper('file');
						$config = read_file(BASEPATH.'../sparks/sandcastle-cms/'.$this->version.'/config/sandcastle_cms.php');
						$config = str_replace('$config[\'sandcastle_cms\'][\'is_installed\'] = FALSE;', '$config[\'sandcastle_cms\'][\'is_installed\'] = FALSE;', $config);

						// write changes to config and check
						if(!write_file(BASEPATH.'../sparks/sandcastle-cms/'.$this->version.'/config/sandcastle_cms.php')){
							show_error('Failed to update config file. Please set <code>$config[\'sandcastle_cms\'][\'is_installed\'] = TRUE;</code>.');
							return FALSE;
						}

						return TRUE;
					}
					else {
						show_error('Unknown installation error.');
					}
				}
				else {
					show_error('Failed to copy <code>./sparks/sandcastle-cms/'.$this->version.'/controllers/cms.php</code> to <code>./application/controllers/cms.php</code>.');
				}
			}
			else {
				show_error('Failed to create <code>./content/</code>.');
			}

			// reset some folder perms
			@chmod(BASEPATH.'../', $root_dir);
			@chmod(APPPATH.'controllers/', $controller_dir);

			return FALSE;
		}
	}