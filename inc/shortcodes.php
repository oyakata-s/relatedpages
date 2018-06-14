<?php
/*
 * ショートコード関連
 */

/*
 * 関連ページを出力する
 * [relatedpages]
 */
function shortcode_relatedpages( $atts ) {
	extract( shortcode_atts( array(
		'id' => get_the_ID(),
	), $atts ) );
	return get_the_relatedpages( $id );
}
add_shortcode( 'relatedpages', 'shortcode_relatedpages' );

?>
