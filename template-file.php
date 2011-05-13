<?php
/**
 * Main class for module
 *
 * @author Valera Satsura (http://www.odesk.com/users/~~41ba9055d0f90cee)
 * @copyright Positive Sum (http://positivesum.org)
 */

class cfct_module_option_template_file extends cfct_module_option {
	// Template filename
	public $template_file = null;

	public function __construct() {
		// Register new item in config menu for every module
		parent::__construct('Set Template', 'template-file');

		// Add filter to hook "cfct-build-module-class"
		add_filter('cfct-build-module-class', array($this, 'apply_classes'), 10, 2);
	}

	/**
	 * Intermediate filter.
	 * Need for run filter on parent module.
	 *
	 * @param  $class
	 * @param  $data
	 * @return void
	 */
	public function apply_classes($class, $data) {
		// "template-file" key exists?
		if (isset($data['cfct-module-options'][$this->id_base]['template-file'][0])) {
			$this->template_file = $data['cfct-module-options'][$this->id_base]['template-file'][0];
		}
		// Get id_base from parent module (module, not option)
		$id_base = array_pop(explode(' ', $class));
		// Run display hook for parent module
		add_filter('cfct-module-'.$id_base.'-view', array($this, 'apply_template_file'));

		return $class;
	}

	/**
	 * Main filter to change view template file
	 *
	 * @param  $view
	 * @param  $data
	 * @return string
	 */
	public function apply_template_file($view, $data='') {
		// If not setup template file in wp-admin
		if (empty($this->template_file)) {
			return $view;
		}

		// If template file setup
		$filename = get_stylesheet_directory().DIRECTORY_SEPARATOR.$this->template_file.'.php';

		// Check if file exists
		if (false == is_file($filename)) {
			return $view;
		}

		return $filename;
	}

	/**
	 * Admin form
	 *
	 * @param  $data
	 * @param  $module_type
	 * @return string
	 */
	public function form($data, $module_type) {                
		$dropdown_opts = apply_filters('cfct-module-predefined-class-options', cfct_class_groups('wrapper'));
		$predefined_classes = array();
		$input_class = (empty($dropdown_opts) ? 'no-button' : null);

        file_put_contents(WP_CONTENT_DIR.'/debug.log', $module_type);

		$value = null;
		if (!empty($data['template-file'])) {
			$value = implode(' ', array_map('esc_attr', $data['template-file']));
		}

        $templates = $this->get_templates($module_type);

        $templates_html = '<select class="'.$input_class.'" name="'.$this->get_field_name('template-file').'" id="'.$this->get_field_id('template-file').'">';
        foreach ($templates as $t_file => $t_data) {
            $selected = '';
            if ($t_file == $value) {
                $selected = 'selected';
            }
            $templates_html .= '<option value="'.$t_file.'" '.$selected.'>'.$t_data['Name'].'</option>';
        }
        $templates_html .= '</select>';

		$html = '
				<label for="'.$this->get_field_id('template-file').'">Template:</label>
				<div class="cfct-select-menu-wrapper">';
        $html .= $templates_html;

		if (is_array($dropdown_opts) && !empty($dropdown_opts)) {
			$html .= '<input type="button" name="" id="'.$this->get_field_id('class-list-toggle').'" class="cfct-button cfct-button-dark" value="">
					 <div id="'.$this->get_field_id('class-list-menu').'" class="cfct-select-menu" style="display: none;">
					 <ul>';

		foreach($dropdown_opts as $classname => $title) {
			$class = (in_array($classname, $data['template-file']) ? 'inactive' : null);
			$html .= '
							<li><a class="'.$class.'" href="#'.esc_attr($classname).'" title="'.esc_attr($title).'">'.esc_html($classname).'</a></li>';
		}
		$html .= '
						</ul>
					</div>';
		}
		$html .= '
				</div>
			';
		return $html;
	}

	/**
	 * Admin JS
	 * @return string
	 */
	public function admin_js() {
		$js = '
// Module Extra: Template File
	// show/hide the pre-defined css list from toggle button
	$("#'.$this->get_field_id('class-list-toggle').'").live("click", function() {
		var tgt = $(this).siblings("div.cfct-select-menu");

		// check to see if any pre-defined class names need toggling before opening the drawer
		if (tgt.is(":hidden")) {
			toggle_css_module_options_list_use();
		}

		tgt.toggle();
		return false;
	});

	// show the pre-defined css list when input is focused
	$("#'.$this->get_field_id('template-file').'").live("click", function(e) {
		var tgt = $(this).siblings("div.cfct-select-menu");
		if (tgt.is(":hidden")) {
			toggle_css_module_options_list_use();
			tgt.show();
		}
		return false;
	});

	$("#'.$this->get_field_id('template-file').'").live("keyup", function() {
		setTimeout(toggle_css_module_options_list_use, 200);
	});

	// catch a click in the popup and close the flyout
	$("#cfct-popup").live("click", function(){
		$("#'.$this->get_field_id('class-list-menu').':visible").hide();
	});

	var toggle_css_module_options_list_use = function() {
		var classes = $("#'.$this->get_field_id('template-file').'").val().split(" ");
		$("#'.$this->get_field_id('class-list-menu').' a").each(function(){
			var _this = $(this);
			if ($.inArray(_this.text(),classes) == -1) {
				_this.removeClass("inactive");
			}
			else {
				_this.addClass("inactive");
			}
		});
	}

	// insert the clicked item in to the text-input
	$("#'.$this->get_field_id('class-list-menu').' a").live("click", function(e) {
		_this = $(this);
		if (!_this.hasClass("inactive")) {
			_this.addClass("inactive");
			var tgt = $("#'.$this->get_field_id('template-file').'");
			tgt.val(tgt.val() + " " +_this.text());
		}
		return false;
	});

	$("#'.$this->get_field_id('class-list-menu').'").live("click", function() {
		return false;
	});
			';
		return $js;
	}

    public function admin_css() {
        $css = '#template-file-template-file { width: 200px; }';

        return $css;
    }

	/**
	 * Update data
	 *
	 * @param  $new_data
	 * @param  $old_data
	 * @return array
	 */
	public function update($new_data, $old_data) {
		$ret = array();

		$classes = explode(' ', $new_data['template-file']);
		if (is_array($classes)) {
			foreach($classes as $class) {
				$ret['template-file'][] = sanitize_title_with_dashes(trim(strip_tags($class)));
			}
		}

		return $ret;
	}

    private function get_templates($module_type) {
        // Current path for theme
        $theme_path = get_stylesheet_directory();
        // Search templates files started with "wp-cb-"
        $files = glob($theme_path.'/cfct-*.php');
        // Sweet module name
        $module_name = str_replace('_', '-', $module_type);
        // Templates for current module
        $templates = array();
        // Template Headers
        $template_headers = array(
            'Name' => 'Template Name',
            'Package' => 'Template Package',
            'Description' => 'Template Description'
        );
        // Search default template
        $default_template = '';
        $default_template_data = array(
            'Name' => 'Default',
            'Package' => $module_name,
            'Description' => 'Default template'
        );
        if (file_exists($theme_path.'/'.$module_name.'.php')) {
            $default_template = $module_name;
            $default_template_data = get_file_data($theme_path.'/'.$module_name.'.php', $template_headers, 'template-file');
        }
        $templates[$default_template] = $default_template_data;

        // Search additional templates
        foreach ($files as $file) {
            $data = get_file_data($file, $template_headers, 'template-file');
            if ($data['Package'] == $module_name) {
                $filename = str_replace('.php', '', basename($file));
                $templates[$filename] = $data;
            }
        }

        return $templates;
    }
}

cfct_module_register_extra('cfct_module_option_template_file');