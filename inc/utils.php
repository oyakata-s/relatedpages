<?php
/*
 * 共通関数
 */

/*
 * テーマで使用できる投稿タイプ
 * page,post,カスタム投稿
 */
function get_theme_post_types() {
	$post_types = get_post_types( array('public' => true ), 'names' );
	unset($post_types['attachment']);
	return $post_types;
}

/*
 * get_post_image_urlがfalse（画像なし）の場合
 * RELATEDP_NOIMAGEを返すフィルター
 */
function custom_noimage_url( $imgsrc ) {
	if (!$imgsrc) {
		$imgsrc = RELATEDP_NOIMAGE;
	}
	return $imgsrc;
}

/*
 * blogurlからホスト名取得
 */
if ( !function_exists('get_urlhostname') ) {
 function get_urlhostname() {
	$info = parse_url(get_bloginfo('url'));
	return $info['host'];
}
}

/*
 * 投稿にアイキャッチ画像があればURLを返す
 * なければfalse
 * filter post_image_urlでカスタム可
 */
if ( !function_exists('get_post_image_url') ) {
function get_post_image_url( $size = 'thumbnail', $content_search = false, $post_id = null ) {
	global $post;
	if ( is_null($post_id) ) {
		$post_id = $post->ID;
	}

	$imgsrc = get_the_post_thumbnail_url($post_id, $size);
	if ( !$imgsrc ) {
		if ($content_search) {
			$args = array(
				'post_mime_type' => 'image',
				'post_parent' => $post_id,
				'post_type' => 'attachment',
				'numberposts' => 1,
				);
			$image = get_children($args);
			if (!empty($image)) {
				$post_img = wp_get_attachment_image_src( key($image) , $size );
				$imgsrc = $post_img[0];
			} else {
				$imgsrc = get_content_imgsrc($post_id);
			}
		}
	}

	return apply_filters('post_image_url', $imgsrc);
	// return $imgsrc;
}
}

/*
 * 投稿本文にimgタグがあればurlを返す
 * なければ	false
 */
if ( !function_exists('get_content_imgsrc') ) {
function get_content_imgsrc( $post_id = null ) {
	global $post, $posts;

	$obj = $post;
	if ( !is_null($post_id) ) {
		$obj = get_post($post_id);
	}

	// $imgsrc = '';
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $obj -> post_content, $matches);
	$imgsrc = $matches[1][0];

	if (empty($imgsrc)) {//Defines a default image
		$imgsrc = false;
	}

	return $imgsrc;
}
}

/*
 * 文字列トリミング
 */
if ( !function_exists('ft_substr') ) {
function ft_substr($str, $length, $start = 0 ) {
	$encoding = get_bloginfo('charset');
	if (mb_strlen($str, $encoding) <= $length) {
		return $str;
	} else {
		$substr = mb_substr($str, $start, $length, $encoding);
		$substr .= '...';
		return $substr;
	}
}
}

/*
 * テキストからタグやショートコードを除去
 */
if ( !function_exists('clean_text') ) {
function clean_text( $text ) {
	$clean = '';
	$clean = strip_tags($text);
	$clean = strip_shortcodes($clean);
	$clean = preg_replace('#<img (.*?)>#i', ' ', $clean);
	$clean = str_replace(array("\r\n","\r","\n"), ' ', $clean);
	return $clean;
}
}

?>
