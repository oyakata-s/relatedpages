<?php
/*
 * 自作テーマ、プラグインの設定用ベースクラス
 */

if ( ! class_exists( 'FtSetting' ) ) {
class FtSetting {

	protected $prefix = null;
	protected $options = null;

	/*
	 * コンストラクタ
	 */
	public function __construct( $prefix, $options = null ) {
		$this->prefix= $prefix;
		$this->options = $options;
	}

	/* 
	 * オプションページ追加
	 */
	public function registerOptionsPage(
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$optionpage_template ) {
		add_options_page(
				$page_title, $menu_title, $capability, $menu_slug,
				function () use ( $optionpage_template ) {
					if ( ! current_user_can( 'manage_options' ) ) {
						wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
					}				
					require_once $optionpage_template;
				} );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
	}
	
	/* 
	 * 設定登録
	 */
	public function registerSettings() {
		$settings_group = $this->prefix. '_settings_group';
		foreach ( $this->options as $key => $value ) {
			register_setting( $settings_group, $key );
		}
	}

	/* 
	 * 設定値を追加
	 */
	public function addOption( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/* 
	 * オプション設定値を取得
	 */
	public function getOption( $key ) {
		return $this->options[ $key ];
	}

}
}

?>
