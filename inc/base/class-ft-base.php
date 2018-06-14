<?php
/*
 * 自作テーマ、プラグインのベースクラス
 */

if ( ! class_exists( 'FtBase' ) ) {
class FtBase {

	protected $basefile = null;
	protected $version = 0.1;
	protected $setting = null;

	/*
	 * コンストラクタ
	 */
	public function __construct( $file ) {
		if ( empty( $file )  ) {
			error_log( 'FtBase construction failed.' );
			throw new Exception( 'FtBase construction failed.' );
		}
		$this->basesfile = $file;

		$data = get_file_data( $file, array( 'version' => 'Version' ) );
		$this->version = $data['version' ];
	}

	public function setOptions( $setting ) {
		$this->setting = $setting;
	}

	/* 
	 * バージョン取得
	 */
	public function getVersion() {
		if ($this->version < '1.0') {
			return date('0.Ymd.Hi');
		} else {
			return $this->version;
		}
	}

	/* 
	 * オプション設定値を取得
	 */
	public function getOption( $key, $default = null ) {
		$value = get_option( $key );
		if ( $value && ! empty( $value ) ) {
			return $value;
		} else if ( ! is_null( $default ) ) {
			return $default;
		} else {
			return $this->setting->getOption( $key );
		}
	}

}
}

?>
