<?php
/*
 * 管理画面用
 */

/*
 * 管理画面設定
 */
function relatedp_admin_init() {
	// 設定メニュー追加
	add_action( 'admin_menu', 'add_menu_relatedpsetting' );

	// 管理画面用script
	$hook_sfx = 'settings_page_plugin_relatedp_options';
	add_action( 'admin_print_styles-'.$hook_sfx, 'relatedp_admin_print_style' );
	add_action( 'admin_print_scripts-'.$hook_sfx, 'relatedp_admin_print_script' );
	add_action( 'admin_enqueue_scripts', 'relatedp_admin_enqueue_script' );

	// 特徴語フィールド追加
	add_action( 'init', 'add_taxonomy_features' );
	add_action( 'save_post', 'save_features');

	// ajax通信用
	add_action( 'wp_ajax_relatedp_update', 'relatedp_update' );	// 一括更新用
}

/*
 * タクソノミー：特徴語追加
 */
function add_taxonomy_features() {

	// （タグのような）階層のないカスタム分類を新たに追加
	$labels = array(
		'name'					=> __( 'Feature Words', 'relatedpages' ),
		'menu_name'				=> __( 'Feature Words', 'relatedpages' ),
	);

	$args = array(
		'hierarchical'			=> false,
		'labels'				=> $labels,
		'public'				=> false,
		'show_ui'				=> true,	// 投稿画面に表示するかしないか
		'show_admin_column'		=> false,
		'update_count_callback'	=> '_update_post_term_count',
		'rewrite'				=> array( 'slug' => 'features' ),
	);

	register_taxonomy( 'features', get_related_post_types(), $args );
}

/*
 * 特徴語を保存
 */
function save_features( $post_id ) {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return $post_id; }
	if ( !current_user_can('edit_post', $post_id) ) { return $post_id; }

	$features = get_feature_from_content($_POST['post_title'] . ',' . $_POST['content']);

	// 特徴語を削除→追加
	wp_delete_object_term_relationships($post_id, 'features');
	wp_set_post_terms($post_id, $features, 'features', true);
}

/*
 * 設定メニューに追加
 */
function add_menu_relatedpsetting() {
	add_options_page(
		__('Related Pages Setting', 'relatedpages'),
		__('Related Pages Setting', 'relatedpages'),
		'manage_options',
		'plugin_relatedp_options',
		'create_relatedp_options');
	add_action('admin_init', 'register_relatedp_settings');
}
function register_relatedp_settings() {
	register_setting('relatedp_settings_group', 'relatedp_yahoo_appid');
	register_setting('relatedp_settings_group', 'relatedp_heading_text');
	register_setting('relatedp_settings_group', 'relatedp_number_post');
	register_setting('relatedp_settings_group', 'relatedp_feature_type');
	$post_types = get_theme_post_types();
	foreach ( $post_types as $post_type ) {
		register_setting('relatedp_settings_group', 'relatedp_posttype_'.$post_type);
	}
	register_setting('relatedp_settings_group', 'relatedp_sametype_only');
	register_setting('relatedp_settings_group', 'relatedp_default_img');
	register_setting('relatedp_settings_group', 'relatedp_container_class');
	register_setting('relatedp_settings_group', 'relatedp_heading_tag');
	register_setting('relatedp_settings_group', 'relatedp_heading_class');
	register_setting('relatedp_settings_group', 'relatedp_grouping_class');
	register_setting('relatedp_settings_group', 'relatedp_element_class');
	register_setting('relatedp_settings_group', 'relatedp_use_css');
	register_setting('relatedp_settings_group', 'relatedp_css_custom');
}
function create_relatedp_options() {
	if ( !current_user_can('manage_options') ) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	require_once RELATEDP_DIR_PATH . 'parts/admin-relatedp.php';
}

/*
 * 管理画面のみ必要なstyle読み込み
 */
function relatedp_admin_print_style() {
	wp_enqueue_style('fontawesome',
		'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
		array(), '4.7.0', 'all');
	wp_enqueue_style( 'plugin-relatedp',
		RELATEDP_DIR_URL . 'css/admin-style.css',
		array(), get_relatedp_version(), 'all' );
}

/*
 * 管理画面のみ必要なJS読み込み
 */
function relatedp_admin_enqueue_script( $hook_suffix ) {
	if ($hook_suffix == 'settings_page_plugin_relatedp_options') {
		wp_enqueue_media();
		wp_enqueue_script( 'relatedp-admin_script',
			RELATEDP_DIR_URL.'js/admin_script.js',
			array('jquery'), get_relatedp_version());
		wp_enqueue_script( 'relatedp-update-posts',
			RELATEDP_DIR_URL.'js/update_posts.js',
			array('jquery'), get_relatedp_version());
		wp_enqueue_script( 'relatedp-image-upload',
			RELATEDP_DIR_URL.'js/media_upload.js',
			array('media-upload'), get_relatedp_version());
	}
}

/*
 * 管理画面のみ必要なJS出力
 */
function relatedp_admin_print_script() {
?>
<script type='text/javascript'>
	var updating_dlg = '処理中…';
	var updated_dlg = '設定完了';
	var updated_msg = ' ページ設定しました！';
	var container_class = '<?php echo get_option("relatedp_container_class"); ?>';
	var heading_tag = '<?php echo get_relatedp_option("relatedp_heading_tag", "h3"); ?>';
	var heading_class = '<?php echo get_option("relatedp_heading_class"); ?>';
	var heading_text = '<?php echo get_relatedp_option("relatedp_heading_text", "Related Pages"); ?>';
	var group_class = '<?php echo get_option("relatedp_grouping_class"); ?>';
	var element_class = '<?php echo get_option("relatedp_element_class"); ?>';
	var noimage_url = '<?php echo RELATEDP_NOIMAGE; ?>';
	var page_cnt = <?php echo get_relatedp_option('relatedp_number_post', 4); ?>;
</script>
<?php
}

?>
