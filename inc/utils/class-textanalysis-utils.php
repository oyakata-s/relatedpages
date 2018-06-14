<?php
/*
 * lastfmを扱うクラス
 */
require_once RELATEDP_DIR_PATH . 'inc/utils/class-cache-utils.php';	// キャッシュ使用

if ( ! class_exists( 'TextAnalysisUtils' ) ) {
class TextAnalysisUtils {

	static private $instance = null;	// インスタンス

	private $appId = null;
	private $type = null;
	private $cache = null;

	/*
	 * コンストラクタ
	 */
	public function __construct( $app_id, $parse_type = 'keyphrase', $cache_dir = null, $cache_expire = null ) {
		if ( empty( $app_id ) ) {
			throw new Exception( 'TextAnalysisUtils construction failed.(appId:' . $app_id . ')' );
		}
		try {
			$this->appId = $app_id;
			$this->type = $parse_type;
			$this->cache = CacheUtils::getInstance( $cache_dir, $cache_expire );
		} catch ( Exception $e ) {
			throw $e;
		}
	}

	/*
	 * インスタンスを取得
	 */
	public static function getInstance( $app_id, $parse_type = 'keyphrase', $cache_dir = null, $cache_expire = null ) {
		if ( empty( $instance ) ) {
			try {
				$instance = new TextAnalysisUtils( $app_id, $parse_type, $cache_dir, $cache_expire );
			} catch ( Exception $e ) {
				throw $e;
			}
		}
		return $instance;
	}

	/* 
	 * 特徴後を取得
	 */
	public function getFeatures( $text ) {
		$text = $this->cleanText( $text );

		$features = array();
		if ( $this->type === 'keyphrase' ) {
			$features = $this->getKeyPhrase( $text );
		} else {
			$features = $this->getFreqWords( $text );
		}

		return $features;
	}

	/* 
	 * キーフレーズ取得
	 */
	private function getKeyPhrase( $text ) {
		$features = array();
		try {
			$text = mb_substr( $text, 0, 500, get_bloginfo( 'charset' ) );
			$url = 'https://jlp.yahooapis.jp/KeyphraseService/V1/extract'
				. '?appid=' . $this->appId
				. '&output=php'
				. '&sentence=' . rawurlencode( $text );
			$data = $this->cache->getContents( $url, 'keyphrase-' . md5( $text ) . '.serialize' );
			$data = unserialize( $data );

			foreach( $data as $key => $value ){
				$features[] = $key;
			}
			$features = array_slice( $features, 0, 10 );
			return $features;
		} catch ( Exception $e ) {
			return $features;
		}
	}

	/* 
	 * 出現頻度の高い単語を取得
	 */
	private function getFreqWords( $text ) {
		$features = array();
		try {
			$url = 'https://jlp.yahooapis.jp/MAService/V1/parse'
			. '?appid=' . $this->appId
			. '&results=uniq'
			. '&filter=' . rawurlencode('1|2|3|9')
			. '&sentence=' . rawurlencode( $text );
			$xml = $this->cache->getContentsUrl( $url, 'freqword-' . md5( $text ) . '.serialize' );
			$parse = simplexml_load_file( $xml );

			foreach( $parse->uniq_result->word_list->word as $value ){
				$surface = (string)$value->surface;
				if ( ! preg_match('/^[0-9０-９,\.]+$/', $surface ) ) {
					$features[] = $surface;
				}
			}
			$features = array_slice( $features, 1, 5 );
			return $features;
		} catch ( Exception $e ) {
			return $features;
		}
	}

	/* 
	 * タグ、ショートコード、画像、改行を削除
	 */
	private function cleanText( $text ) {
		$clean = '';
		$clean = strip_tags( $text );
		$clean = strip_shortcodes( $clean );
		$clean = preg_replace( '#<img (.*?)>#i', ' ', $clean );
		$clean = str_replace( array("\r\n","\r","\n"), ' ', $clean );
		return $clean;
	}

}
}

?>
