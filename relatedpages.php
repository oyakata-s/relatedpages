<?php
/*
 * Plugin Name: Something Related Pages
 * Plugin URI: https://github.com/oyakata-s/relatedpages
 * Description: Related Pages plugin
 * Version: 0.2.2
 * Author: oyakata-s
 * Author URI: https://something-25.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: relatedpages
 */

/*
 * 定数定義
 */
define( 'RELATEDP_FILE', __FILE__ );								// プラグインファイルへのパス
define( 'RELATEDP_DIR_PATH', plugin_dir_path( __FILE__ ) );			// プラグインディレクトリへのパス
define( 'RELATEDP_DIR_URL', plugin_dir_url( __FILE__ ) );			// プラグインディレクトリへのURL
define( 'RELATEDP_TEXTDOMAIN', 'relatedpages' );					// テキストドメイン

define( 'RELATEDP_CACHE_DIR_PATH', RELATEDP_DIR_PATH.'cache/' );	// キャッシュ用ディレクトリパス
define( 'RELATEDP_CACHE_EXPIRE', 86400 );					// キャッシュ有効期間規定値

/*
 * ライブラリ読込
 */
require_once ABSPATH . 'wp-admin/includes/file.php';		// WP_Filesystem使用
require_once RELATEDP_DIR_PATH . 'inc/setting.php';			// 設定関連
require_once RELATEDP_DIR_PATH . 'inc/shortcodes.php';		// ショートコード用
require_once RELATEDP_DIR_PATH . 'inc/template-tags.php';		// ショートコード用

require_once RELATEDP_DIR_PATH . 'inc/utils/class-textanalysis-utils.php';	// テキスト解析用
require_once RELATEDP_DIR_PATH . 'inc/ajax/class-updaterelated-ajax.php';	// 特徴語一括更新用

require_once RELATEDP_DIR_PATH . 'inc/base/class-ft-base.php';			// 初期化関連
require_once RELATEDP_DIR_PATH . 'inc/base/class-ft-utils.php';			// ユーティリティ関連

class RelatedPages extends FtBase {

	/*
	 * 初期化
	 */
	public function __construct() {
		
		/*
		 * ベースクラスのコンストラクタ呼び出し
		 */
		try {
			parent::__construct( RELATEDP_FILE );
		} catch ( Exception $e ) {
			throw $e;
		}

		// 多言語翻訳用
		load_plugin_textdomain( 'relatedpages', false, 'relatedpages/languages' );

		// 設定
		$this->setting = new RelatedPagesSetting();

		register_activation_hook( RELATEDP_FILE, array( $this, 'activation' ) );		
		register_deactivation_hook( RELATEDP_FILE, array( $this, 'deactivation' ) );

		add_action( 'init', array( $this, 'registerFeature' ) );
		add_action( 'save_post', array( $this, 'saveFeature' ) );
		add_action( 'wp_head', array( $this, 'addHead' ) );
	}

	/* 
	 * プラグイン有効化
	 */
	public function activation() {
		// キャッシュディレクトリの準備
		FtUtils::checkDirectory( RELATEDP_CACHE_DIR_PATH );
	}

	/* 
	 * プラグイン無効化
	 */
	public function deactivation() {
		// キャッシュディレクトリの削除
		FtUtils::removeDirectory( RELATEDP_CACHE_DIR_PATH );
	}

	/* 
	 * head追加
	 */
	public function addHead() {
		$use = $this->getOption( 'relatedp_use_css' );
		if ( ! $use ) {
			return;
		}

		$css = $this->getOption( 'relatedp_css_custom' );
		if ( ! $css ) {
			if ( WP_Filesystem() ) {
				global $wp_filesystem;
				$css = $wp_filesystem->get_contents( RELATEDP_DIR_PATH . 'css/style.css' );
			}
		}

		echo '<style type="text/css" id="relatedpages_style">';
		echo $css;
		echo '</style>';
	}

	/* 
	 * カスタム分類（特徴語）追加
	 */
	public function registerFeature() {
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

		register_taxonomy( 'features', $this->getRelatedPostTypes(), $args );
	}

	/* 
	 * 投稿に対して特徴語を保存
	 */
	public function saveFeature( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
		if ( !current_user_can('edit_post', $post_id) ) { return $post_id; }

		$textanalyser = TextAnalysisUtils::getInstance(
			$this->getOption( 'relatedp_yahoo_appid' ),
			$this->getOption( 'relatedp_feature_type' ),
			RELATEDP_CACHE_DIR_PATH,
			RELATEDP_CACHE_EXPIRE
		);
		$features = $textanalyser->getFeatures( $_POST['post_title'] . ',' . $_POST['content'] );

		// 特徴語を削除→追加
		wp_delete_object_term_relationships( $post_id, 'features' );
		wp_set_post_terms( $post_id, $features, 'features', true );
	}

	public function getRelatedPostTypes( $post_id = null ) {
		$related_post_types = array();

		if ( $this->getOption( 'relatedp_sametype_only' ) === 'on' && ! is_null( $post_id ) ) {
			// 「自分と同じ投稿タイプ」が設定されている場合
			$post_type = get_post_type( $post_id );
			$related_post_types[] = $post_type;
		} else {
			// テーマで使用できる投稿タイプを取得
			$post_types = FtUtils::getSuppotedPostTypes();
			foreach ( $post_types as $post_type ) {
				if ( $this->getOption( 'relatedp_posttype_' . $post_type ) ) {
					$related_post_types[] = $post_type;
				}
			}
		}

		return $related_post_types;
	}

}

$relatedp = new RelatedPages();

$updaterelated = new UpdateRelatedRunner( 'relatedp_update' );

?>
