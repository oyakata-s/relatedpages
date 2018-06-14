<?php
/*
 * 自作テーマ、プラグインのajax用ベースクラス
 */

if ( ! class_exists( 'FtAjaxRunner' ) ) {
abstract class FtAjaxRunner {

	protected $action = null;
	protected $nopriv = null;
	protected $nonce = null;

	/*
	 * コンストラクタ
	 */
	public function __construct( $action, $nopriv = false, $nonce = false ) {
		if ( preg_match( '/^[a-zA-Z0-9_\-]+$/', $action ) ) {
			$this->action = $action;
		} else {
			wp_die( "Invalid strings for \$action." );
		}

		if ( $nonce ) {
			$this->nonce = $nonce;
		} else {
			$this->nonce = $action;
		}

		add_action( 'wp_ajax_'.$action, array( $this, 'runner' ) );
		if ( $nopriv ) {
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'runner' ) );
		}
	}

	/* 
	 * ajax処理本体
	 */
	abstract protected function run();

	/* 
	 * ajax処理を実行する
	 */
	public function runner() {
		nocache_headers();

		// if ( wp_verify_nonce( $_POST['nonce'], $this->nonce ) ) {
			$res = $this->run();

			if ( is_array( $res ) ) {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( $res );
			} else {
				echo $res;
			}
		// } else {
		// 	header( 'HTTP/1.1 403 Forbidden' );
		// 	echo '{}';
		// }
		die();
	}

}
}

?>
