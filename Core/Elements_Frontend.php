<?php

namespace SHORTCODE_ADDONS\Core;

/**
 * Description of Elements_Frontend
 *
 * @author biplo
 */

/**
 * Description of Elements
 *
 * @author biplo
 */

use SHORTCODE_ADDONS\Helper\Database as Database;

class Elements_Frontend extends Database
{

	use \SHORTCODE_ADDONS\Helper\Admin_Scripts;
	use \SHORTCODE_ADDONS\Layouts\Elements\Database;
	use \SHORTCODE_ADDONS\Layouts\Elements\Template;

	/**
	 * Store Elements Active Templates.
	 *
	 * @since 2.0.0
	 */
	public $active_templates;

	/**
	 * Current Elements type.
	 *
	 * @since 2.0.0
	 */
	public $oxitype;

	/**
	 * check if oxi import is true or false
	 *
	 * @since 2.0.0
	 */
	public $oxiimport;

	/**
	 * All templates list of current element
	 *
	 * @since 2.0.0
	 */
	public $templates;

	public function elements()
	{

		do_action('shortcode-addons/before_init');
		$this->oxitype = (!empty($_GET['oxitype']) ? ucfirst(sanitize_text_field($_GET['oxitype'])) : '');
		$this->oxiimport = (!empty($_GET['oxiimport']) ? sanitize_text_field($_GET['oxiimport']) : '');
		$this->admin();
		$this->rander();
	}




	public function admin()
	{
		$this->admin_elements_scripts();
		$this->database_data();
		$this->pre_active_check();
	}

	/**
	 * Shortcode Addons Rander.
	 *
	 * @since 2.1.0
	 */
	public function rander()
	{
?>
		<div class="wrap">
			<div class="oxi-addons-wrapper">
				<?php
				apply_filters('shortcode-addons/admin_menu', false);
				if ($this->oxiimport == 'import') :
					$this->elements_import();
				else :
					$this->elements_home();
				endif;
				?>
			</div>
		</div>
	<?php
	}

	public function rec_listFiles($from = '.')
	{
		if (!is_dir($from)) {
			return false;
		}

		$files = [];
		if ($dh = opendir($from)) {
			while (false !== ($file = readdir($dh))) {
				// Skip '.' and '..'
				if ($file == '.' || $file == '..')
					continue;
				$path = $from . '/' . $file;
				if (is_dir($path)) {
					$files += $this->rec_listFiles($path);
				} else {
					if (strpos($path, 'json') !== false) {
						$files[] = file_get_contents($path);
					}
				}
			}
			closedir($dh);
		}
		ksort($files);
		return $files;
	}

	public function templates()
	{
		return $this->rec_listFiles(SA_ADDONS_UPLOAD_PATH . $this->oxitype . '/Layouts');
	}


	/**
	 * Shortcode Addons Element home.
	 *
	 * @since 2.1.0
	 */
	public function elements_home()
	{
		apply_filters('shortcode-addons/support-and-comments', false);
		echo _('<div class="oxi-addons-row">
                    <div class="oxi-addons-wrapper">
                        <div class="oxi-addons-import-layouts">
                            <h1>Shortcode Addons ›
                                ' . $this->admin_name_validation($this->oxitype) . '
                            </h1>
                            <p> View our  ' . $this->admin_name_validation($this->oxitype) . ' from Demo and select Which one You Want</p>
                        </div>
                    </div>');
		echo $this->pre_created_templates();
		echo _(' </div>');
	?>

		<div class="oxi-addons-row">


			<?php
			$i = 0;
			$templatenai = false;
			foreach ($this->templates() as $value) {
				$settings = json_decode($value, true);
				$layouts = str_replace('-', '_', ucfirst($settings['style']['style_name']));
				if (array_key_exists($layouts, $this->pre_active_check())) :
					$i++;
					echo $this->template_rendar($settings);
				else :
					$templatenai = true;
				endif;
			}
			if ($i < 1) :
				$this->pre_active_check(true);
			endif;
			if ($templatenai) :


				echo _('<div class="oxi-addons-col-1 oxi-import">
                        <div class="oxi-addons-style-preview">
                            <div class="oxilab-admin-style-preview-top">
                                <a href="' . admin_url("admin.php?page=shortcode-addons&oxitype=$this->oxitype&oxiimport=import") . '">
                                    <div class="oxilab-admin-add-new-item">
                                        <span>
                                            <i class="fas fa-plus-circle oxi-icons"></i>  
                                            Add More Templates
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>');

			endif;
			echo _('<div class="modal fade" id="oxi-addons-style-create-modal" >
                        <form method="post" id="oxi-addons-style-modal-form">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">                    
                                        <h4 class="modal-title">' . $this->admin_name_validation($this->oxitype) . ' Settings</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class=" form-group row">
                                            <label for="addons-style-name" class="col-sm-6 col-form-label" oxi-addons-tooltip="Give your Shortcode Name Here">Name</label>
                                            <div class="col-sm-6 addons-dtm-laptop-lock">
                                                <input class="form-control" type="text" value="" id="addons-style-name"  name="addons-style-name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" id="addons-oxi-type" name="addons-oxi-type" value="' . $this->oxitype . '">
                                        <input type="hidden" id="oxi-addons-data" name="oxi-addons-data" value="">
                                        <input type="hidden" id="oxistyleid" name="oxistyleid" value="">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                        <input type="submit" class="btn btn-success" name="addonsdatasubmit" id="addonsdatasubmit" value="Save">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal fade" id="oxi-addons-style-export-modal" >
                        <form method="post" id="oxi-addons-style-export-form">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">                    
                                        <h4 class="modal-title">Export Data</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <textarea id="OxiAddImportDatacontent" class="oxi-addons-export-data-code"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-info OxiAddImportDatacontent">Copy</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>');
			?>

		</div>

<?php
	}
}
