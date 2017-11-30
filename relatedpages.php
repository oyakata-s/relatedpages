<?php
/*
 * Plugin Name: Something Related Pages
 * Plugin URI: https://github.com/oyakata-s/relatedpages
 * Description: Related Pages plugin
 * Version: 0.9.1
 * Author: oyakata-s
 * Author URI: http://something-25.com
 *
 * All files, unless otherwise stated, are released under the GNU General Public License
 * version 3.0 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

/*
 * 定数定義
 */
define( 'RELATEDP_DIR_PATH', plugin_dir_path( __FILE__ ) );			// 本プラグイディレクトリへのパス
define( 'RELATEDP_DIR_URL', plugin_dir_url( __FILE__ ) );			// 本プラグイディレクトリへのURL
define( 'RELATEDP_VERSION', get_relatedp_version() );				// プラグインバージョン
define( 'RELATEDP_APPID', get_relatedp_option('relatedp_yahoo_appid') );		// application id
define( 'RELATEDP_NOIMAGE',
	get_relatedp_option('relatedp_default_img', RELATEDP_DIR_URL.'img/noimage.jpg') );	// noimage画像

/*
 * ライブラリ読込
 */
require_once RELATEDP_DIR_PATH . 'inc/admin.php';			// 管理画面用
require_once RELATEDP_DIR_PATH . 'inc/utils.php';			// 関数定義
require_once RELATEDP_DIR_PATH . 'inc/get_related.php';		// 特徴語関連
require_once RELATEDP_DIR_PATH . 'inc/update_posts.php';	// 非同期ページ更新用

/*
 * ショートコード
 */
function shortcode_related_pages() {
	ob_start();
	include_once RELATEDP_DIR_PATH . 'parts/related_pages.php';
	return ob_get_clean();
}

/*
 * 関連ページを表示
 */
function get_related_pages() {
	include_once RELATEDP_DIR_PATH . 'parts/related_pages.php';
}

/*
 * プラグインバージョン
 */
function get_relatedp_version() {
	$data = get_file_data( __FILE__, array( 'version' => 'Version' ) );
	$version = $data['version' ];
	if ($version < '1.0') {
		return date('0.Ymd.Hi');
	} else {
		return $version;
	}
}

/*
 * 初期化処理
 */
function relatedp_init() {

	// 多言語翻訳用
	load_plugin_textdomain( 'relatedpages', false, 'relatedpages/languages');

	// 管理画面設定
	relatedp_admin_init();

	// ショートコード定義
	add_shortcode( 'related_pages', 'shortcode_related_pages' );

	// スタイル出力
	if (get_relatedp_option('relatedp_use_css', false)) {
		add_action( 'wp_head', 'add_relatedpages_style' );
	}
}
add_action( 'plugins_loaded', 'relatedp_init' );

/*
 * スタイル出力
 */
function add_relatedpages_style() {
	$css = get_option('relatedp_css_custom');
	if (!$css) {
		$css = @file_get_contents(RELATEDP_DIR_PATH . 'css/style.css');
	}
?>
<style type="text/css" id="relatedpages_style">
<?php echo $css; ?>
</style>
<?php
}

/*
 * オプションを取得
 * ※get_option($key, $default)で$defaultが
 * 　取得できないので仮で
 */
function get_relatedp_option($key, $default = false) {
	$value = get_option($key);
	if ($value && !empty($value)) {
		return $value;
	} else {
		return $default;
	}
}

?>
