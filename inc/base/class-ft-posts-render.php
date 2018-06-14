<?php
/* 
 * 投稿（複数）をレンダリングする
 */

if ( ! class_exists( 'FtPostsRender' ) ) {
class FtPostsRender {

	private $container;
	private $containerId;
	private $containerClass;
	private $heading;
	private $headingClass;
	private $headingTag;
	private $grouping;
	private $groupingClass;
	private $postClass;
	private $noImage;

	public function __construct( $args = null ) {
		$default = array(
			'container' => 'div',
			'container_id' => null,
			'container_class' => null,
			'heading' => null,
			'heading_class' => null,
			'heading_tag' => 'h3',
			'grouping' => 'div',
			'grouing_class' => null,
			'post_class' => null,
			'no_image' => null,
		);
		if ( ! empty( $args ) && is_array( $args ) ) {
			$args = array_merge( $default, $args );
		}

		$this->container = $args[ 'container' ];
		$this->containerId = $args[ 'container_id' ];
		$this->containerClass = $args[ 'container_class' ];
		$this->heading = $args[ 'heading' ];
		$this->headingClass = $args[ 'heading_class' ];
		$this->headingTag = $args[ 'heading_tag' ];
		$this->grouping = $args[ 'grouping' ];
		$this->groupingClass = $args[ 'grouping_class' ];
		$this->postClass = $args[ 'post_class' ];
		$this->noImage = $args[ 'no_image' ];
	}

	/* 
	 * レンダリングする
	 */
	public function render( $posts ) {
		echo $this->getHtml( $posts );
	}

	/* 
	 * HTMLを取得
	 */
	public function getHtml( $posts ) {
		$html = '';
		foreach ( $posts as $post ) {
			$html .= $this->getPostHtml( $post );
		}

		if ( ! empty( $this->grouping ) ) {
			$group_html = '<' . $this->grouping . ' class="' . $this->groupingClass . '">';
			$group_html .= $html;
			$group_html .= '</' . $this->grouping . '>';
			$html = $group_html;
		}

		if ( ! empty( $this->heading ) ) {
			$head_html = '<' . $this->headingTag . ' class="' . $this->headingClass . '">'
				. $this->heading
				. '</' . $this->headingTag . '>';
			$html = $head_html . $html;
		}

		if ( ! empty( $this->container ) ) {
			$container_html = '<' . $this->container . ' id="' . $this->containerId . '" class="' . $this->containerClass . '">';
			$html = $container_html . $html;
			$html .= '</' . $this->container . '>';
		}

		return $html;
	}

	/* 
	 * 投稿（一見）を取得
	 */
	protected function getPostHtml( $post ) {
		$content = '';
		$content .= '<figure class="post-thumbnail" style="background-image:url(' . $this->getPostImageUrl( $post->ID ) . ');" ></figure>';
		$content .= '<p class="post-title">' . get_the_title( $post->ID ) . '</p>';
		$html = '<a class="post ' . $this->postClass . '" href="' . get_permalink( $post->ID ) . '">' . $content . '</a>';
		$html = apply_filters( 'post_html', $html );
		return $html;
	}

	/* 
	 * 投稿のアイキャッチまたは添付画像URLを取得
	 */
	private function getPostImageUrl( $post_id ) {
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
			$img_url = $this->noImage;
		}

		return $img_url;
	}

}
}

?>
