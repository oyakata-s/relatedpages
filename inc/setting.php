<?php
/*
 * 設定関連
 */
define( 'RELATEDP_HOOK_SUFFIX', 'settings_page_plugin_relatedp_options' );

require_once RELATEDP_DIR_PATH . 'inc/base/class-ft-setting.php';			// 設定ベースクラス

class RelatedPagesSetting extends  FtSetting {

	/*
	 * 初期化
	 */
	public function __construct() {

		try {
			parent::__construct(
				'relatedp',
				array(
					'relatedp_yahoo_appid' => null,
					'relatedp_number_post' => 4,
					'relatedp_feature_type' => 'keyphrase',
					'relatedp_sametype_only' => true,
					'relatedp_default_img' => RELATEDP_DIR_URL . 'img/noimage.jpg',
					'relatedp_auto_add' => false,
					'relatedp_container_class' => 'relatedpages-container',
					'relatedp_heading_text' => __( 'Related Pages', 'relatedpages' ),
					'relatedp_heading_tag' => 'h3',
					'relatedp_heading_class' => 'relatedpages-head',
					'relatedp_grouping_class' => 'relatedpages',
					'relatedp_element_class' => 'relatedpage',
					'relatedp_use_css' => false,
					'relatedp_css_custom' => null
				) );
		} catch ( Exception $e ) {
			throw $e;
		}

		$post_types = FtUtils::getSuppotedPostTypes();
		foreach ( $post_types as $post_type ) {
			$this->addOption( 'relatedp_posttype_' . $post_type, true );
		}

		add_action( 'admin_menu', array( $this, 'addOptionsPage' ) );

		add_action( 'admin_print_styles-'.RELATEDP_HOOK_SUFFIX, array( $this, 'enqueueStyles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );

	}

	/* 
	 * オプションページ追加
	 */
	public function addOptionsPage() {
		$this->registerOptionsPage(
				__( 'Related Pages Setting', 'relatedpages' ),
				__( 'Related Pages Setting', 'relatedpages' ),
				'manage_options',
				'plugin_relatedp_options',
				RELATEDP_DIR_PATH . 'parts/admin-relatedpages.php'
		);
	}

	/* 
	 * css追加
	 */
	public function enqueueStyles() {
		global $relatedp;
		wp_enqueue_style( 'fontawesome',
			RELATEDP_DIR_URL . 'css/font-awesome.min.css',
			array(),
			'4.7.0',
			'all' );
		wp_enqueue_style( 'plugin-relatedp',
			RELATEDP_DIR_URL . 'css/admin-style.min.css',
			array(), 
			$relatedp->getVersion(),
			'all' );
	}

	/* 
	 * js追加
	 */
	public function enqueueScripts( $hook_suffix ) {
		if ( $hook_suffix == RELATEDP_HOOK_SUFFIX ) {
			global $relatedp;
			wp_enqueue_media();
			wp_enqueue_script( 'relatedp-admin-script',
				RELATEDP_DIR_URL . 'js/admin-script.min.js',
				array('jquery'),
				$relatedp->getVersion(),
				true );
			wp_enqueue_script( 'relatedp-update-posts',
				RELATEDP_DIR_URL . 'js/update-posts.min.js',
				array('jquery'),
				$relatedp->getVersion(),
				true );
			wp_localize_script( 'relatedp-update-posts',
				'relatedp_update',
				array(
					'updating_dlg' => __( 'Updating...', 'relatedpages' ),
					'updated_dlg' => __( 'Updated', 'relatedpages' ),
					'updated_msg' => __( ' pages are updated!', 'relatedpages' ),
					'container_class' => $relatedp->getOption( 'relatedp_container_class '),
					'heading_tag' => $relatedp->getOption( 'relatedp_heading_tag '),
					'heading_class' => $relatedp->getOption( 'relatedp_heading_class '),
					'heading_text' => $relatedp->getOption( 'relatedp_heading_text '),
					'group_class' => $relatedp->getOption( 'relatedp_grouping_class '),
					'element_class' => $relatedp->getOption( 'relatedp_element_class '),
					'noimage_url' => $relatedp->getOption( 'relatedp_default_img '),
					'page_cnt' => $relatedp->getOption('relatedp_number_post' )
				) );
			wp_enqueue_script( 'relatedp-image-upload',
				RELATEDP_DIR_URL . 'js/media-upload.min.js',
				array('media-upload'),
				$relatedp->getVersion(),
				true );
		}
	}

}

?>
