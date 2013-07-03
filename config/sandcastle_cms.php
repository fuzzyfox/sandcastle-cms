<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
	
	$config['sandcastle_cms']['version']		= '0.1.2';
	$config['sandcastle_cms']['is_installed']	= FALSE;

	$config['sandcastle_cms']['site_title']		= 'SandCastle';
	$config['sandcastle_cms']['theme']			= 'default';
	$config['sandcastle_cms']['date_format']	= 'jS M Y';
	$config['sandcastle_cms']['pages_order_by']	= 'alpha';
	$config['sandcastle_cms']['pages_order']	= 'asc';
	$config['sandcastle_cms']['excerpt_length']	= 50;
	$config['sandcastle_cms']['twig_config']	= array(
		'cache'			=> FALSE,
		'autoescape'	=> FALSE,
		'debug'			=> FALSE
	);