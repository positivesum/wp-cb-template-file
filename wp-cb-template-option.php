<?php
/*
Plugin Name: WPCB Template Option
Plugin URI: http://positivesum.org/
Description: Adding new option "Template" to every CB Module.
Version: 0.1
Author: Valera Satsura
Author URI: http://www.odesk.com/users/~~41ba9055d0f90cee
*/

/**
 * Adding new option "Template" to every CB Module.
 *
 * Now we can setup output template for every module from active theme.
 *
 * @author Valera Satsura (http://www.odesk.com/users/~~41ba9055d0f90cee)
 * @copyright Positive Sum (http://positivesum.org)
 */

// cfct_module_register_extra('slideshow-transition-option', 'slideshow_transition_option');

// Include main module class
function wp_cb_template_option() {
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'template-file.php');
}

add_action('cfct-modules-loaded', 'wp_cb_template_option');

 
