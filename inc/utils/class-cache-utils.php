<?php
/*
 * cacheを扱うクラス
 */
require_once ABSPATH . 'wp-admin/includes/file.php';		// WP_Filesystem使用

if ( ! class_exists( 'CacheUtils' ) ) {
class CacheUtils {

	static private $instance = null;	// インスタンス

	private $cacheDir = null;		// キャッシュディレクトリ
	private $cacheExpire = 86400;	// キャッシュ有効期間

	/*
	 * コンストラクタ
	 */
	public function __construct( $cache_dir = null, $cache_expire = 86400 ) {
		if ( empty( $cache_dir ) ) {
			throw new Exception( 'CacheUtils construction failed.' );
		}
		$this->cacheDir = $cache_dir;
		$this->cacheExpire = $cache_expire;
	}

	/*
	 * インスタンスを取得
	 */
	public static function getInstance( $cache_dir = null, $cache_expire = 86400 ) {
		if ( empty( $instance ) ) {
			try {
				$instance = new CacheUtils( $cache_dir, $cache_expire );
			} catch ( Exception $e ) {
				throw $e;
			}
		}
		return $instance;
	}

	/*
	 * キャッシュ消去
	 */
	public function clearCache( $exclude = null ) {
		$deleted = array();
		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			$list = $wp_filesystem->dirlist( $this->cacheDir );
			foreach( (array) $list as $filename => $fileinfo ){
				// 指定のファイルは除外
				if ( ! empty( $exclude ) && is_array( $exclude ) ) {
					if ( in_array( $filename, $exclude ) ) {
						continue;
					}
				}

				// 削除
				if ( 'f' == $fileinfo['type'] ) {
					$file = $this->cacheDir . $filename;
					if ( $wp_filesystem->delete( $file, false, 'f' ) ) {
						$deleted[] = $file;
					}
				}
			}
		}

		return $deleted;
	}

	public function getContentsUrl( $url, $filename ) {
		$cachePath = $this->cacheDir . $filename;
		if ( file_exists( $cachePath ) && filemtime( $cachePath ) + $this->cacheExpire > time() ) {
			return $cachePath;
		} else {
			// キャッシュがないか、期限切れなので取得しなおす FS_CHMOD_FILE
			try {
				$data = $this->getUrlContents( $url );
				if ( $data != false ) {
					// キャッシュに保存
					$this->putCacheContents( $cachePath, $data, FS_CHMOD_FILE );
				}
				return $cachePath;
			} catch ( Exception $e ) {
				throw $e;
			}
		}
	}

	/*
	 * キャッシュがあればキャッシュを
	 * なければURLから取得
	 */
	public function getContents( $url, $filename ) {
		$cachePath = $this->cacheDir . $filename;
		if ( file_exists( $cachePath ) && filemtime( $cachePath ) + $this->cacheExpire > time() ) {
			// キャッシュ有効期間内なのでキャッシュの内容を返す
			return $this->getCacheContents( $cachePath );
		} else {
			// キャッシュがないか、期限切れなので取得しなおす FS_CHMOD_FILE
			try {
				$data = $this->getUrlContents( $url );
				if ( $data != false ) {
					// キャッシュに保存
					$this->putCacheContents( $cachePath, $data, FS_CHMOD_FILE );
				}
				return $data;
			} catch ( Exception $e ) {
				throw $e;
			}
		}

		return false;
	}

	/*
	 * パスから取得
	 */
	private function getCacheContents( $path ) {
		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			return $wp_filesystem->get_contents( $path );
		}
		return false;
	}

	/*
	 * パスに書き込み
	 */
	private function putCacheContents( $path, $data, $mode ) {
		if ( WP_Filesystem() ) {
			global $wp_filesystem;
			return $wp_filesystem->put_contents( $path, $data, $mode );
		}
		return false;
	}

	/*
	 * URLから取得
	 */
	private function getUrlContents( $url, $timeout = 30 ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//タイムアウト時間設定
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );

		//リダイレクトしている場合も読みこむ
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );

		$result = curl_exec( $ch );

		// エラーが発生したら例外を投げる
		if( curl_errno( $ch ) ) {
			$e = new Exception( curl_error( $ch ) );
			curl_close( $ch );
			throw $e;
		}

		curl_close( $ch );
		return $result;
	}
}
}

?>
