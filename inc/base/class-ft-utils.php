<?php
/*
 * ユーティリティクラス
 */
require_once ABSPATH . 'wp-admin/includes/file.php';		// WP_Filesystem使用

if ( ! class_exists( 'FtUtils' ) ) {
class FtUtils {

	public static function getPostImageUrl( $post_id, $size = 'thumbnail', $noimage_url ) {
		$img_url = null;
		if ( has_post_thumbnail( $post_id ) ) {
			$img_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
		}

		if ( empty( $img_url ) ) {
			$args = array(
				'post_mime_type' => 'image',
				'post_parent' => $post_id,
				'post_type' => 'attachment',
				'numberposts' => 1,
				);
			$post_imgs = get_children( $args );
			if ( ! empty( $post_imgs ) ) {
				$post_img = wp_get_attachment_image_src( key( $post_imgs ) , $size );
				$img_url = $post_img[0];
			}
		}

		if ( empty( $img_url ) ) {
			$img_url = $noimage_url;
		}

		return $img_url;
	}

	/* 
	 * サポートしている投稿タイプ
	 */
	public static function getSuppotedPostTypes() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		unset( $post_types[ 'attachment' ] );
		return $post_types;
	}

	/* 
	 * ディレクトリ存在チェック
	 */
	public static function checkDirectory( $dir ) {
		if ( ! file_exists( $dir ) ) {
			return wp_mkdir_p( $dir );
		}

		return false;
	}

	/* 
	 * ディレクトリ削除
	 */
	public static function removeDirectory( $dir ) {
		if ( file_exists( $dir ) ) {
			if ( WP_Filesystem() ) {
				global $wp_filesystem;
				return $wp_filesystem->delete( $dir, true );
			}
		}

		return false;
	}

}
}

?>
