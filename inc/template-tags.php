<?php

/*
 * テンプレートタグ
 */
require_once RELATEDP_DIR_PATH . 'inc/base/class-ft-posts-render.php';

/* 
 * 関連ページを出力する
 */
function the_relatedpages( $post_id = null ) {
	echo get_the_relatedpages( $post_id );
}
function get_the_relatedpages( $post_id = null ) {
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$renderer = new RelatedPagesRender( get_the_ID() );
	return $renderer->getHtml();
}

/* 
 * the_content に自動追加
 */
function add_relatedpages( $html ) {
	global $relatedp;
	
	// 自動追加するか
	$automatic = $relatedp->getOption( 'relatedp_auto_add' );

	// 投稿ページまたは固定ページのみ
	if ( $automatic && ( is_singular() || is_page() ) ) {
		$html .= get_the_relatedpages( get_the_ID() );
	}

	return $html;
}
add_filter( 'the_content', 'add_relatedpages' );

/* 
 * 関連ページを出力するクラス
 */
class RelatedPagesRender extends FtPostsRender {

	private $postId = null;

	public function __construct( $post_id = null ) {
		$this->postId = $post_id;

		global $relatedp;
		parent::__construct( array(
			'container_id' => 'related_pages_container',
			'container_class' => $relatedp->getOption( 'relatedp_container_class' ),
			'heading' => $relatedp->getOption( 'relatedp_heading_text' ),
			'heading_class' => $relatedp->getOption( 'relatedp_heading_class' ),
			'heading_tag' => $relatedp->getOption( 'relatedp_heading_tag' ),
			'grouping_class' => $relatedp->getOption( 'relatedp_grouping_class' ),
			'post_class' => $relatedp->getOption( 'relatedp_element_class' ),
			'no_image' => $relatedp->getOption( 'relatedp_default_img' ),
		) );
	}

	/* 
	 * HTML取得
	 */
	public function getHtml( $posts = null ) {
		global $relatedp;
		$num = $relatedp->getOption( 'relatedp_number_post' );
		$related_posts = $this->getPosts();
		$posts = array_slice( $related_posts, 0, $num );
		return parent::getHtml( $posts );
	}

	/* 
	 * postデータの配列を取得
	 */
	private function getPosts() {
		global $relatedp;
		$appId = $relatedp->getOption( 'relatedp_yahoo_appid' );
		if ( ! empty( $appId ) ) {
			return $this->getRelatedPagesByFeature();
		} else {
			return $this->getRelatedPagesByTaxonomy();
		}

	}

	/* 
	 * 投稿に関連づいたタグ一覧を返す
	 */
	private function getTagIds( $post_id = null ) {
		if ( is_null( $post_id ) ) {
			$post_id = $this->postId;
		}

		$tags = wp_get_post_tags( $post_id );
		$tagIDs = array();
		foreach( $tags as $tag ){
			$tagIDs[] = $tag->term_id;
		}
		return $tagIDs;
	}

	/* 
	 * 特徴語から関連ページを取得
	 */
	private function getRelatedPagesByFeature() {
		global $relatedp;
		$related_post_types = $relatedp->getRelatedPostTypes( $this->postId );

		$post_type = get_post_type( $this->postId );
		$tags = $this->getTagIds();
		$cats = wp_get_post_categories( $this->postId );
		$features = wp_get_post_terms( $this->postId , 'features', array( 'fields'=>'ids' ) );
		$tagIDs = array();
		if ( $tags || $cats || $features ) {
			$args = array(
				'post_type' => $related_post_types,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'oederby' => 'date',
				'post__not_in' => array( $this->postId ),
				'tax_query' => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'category',
						'terms'    => $cats,
						'field'    => 'id',
						'operator' => 'IN'
					),
					array(
						'taxonomy' => 'post_tag',
						'terms'    => $tags,
						'field'    => 'id',
						'operator' => 'IN'
					),
					array(
						'relation' => 'OR',
						'taxonomy' => 'features',
						'terms'    => $features,
						'field'    => 'id',
						'operator' => 'IN'
					)
				)
			);
			$related_posts = get_posts( $args );
		} else {
			$related_posts = array();
		}

		/*
		 * 特徴語、カテゴリ、タグの重複数でソートする
		 */
		if ( $related_posts ) {
			$tmp_p = array();
			foreach ( $related_posts as $p ) {
				$tmp_t = $this->getTagIds( $p->ID );
				$tmp_c = wp_get_post_categories( $p->ID );
				$tmp_f = wp_get_post_terms( $p->ID, 'features', array( 'fields'=>'ids' ) );
				$rate_t = count( array_intersect( $tags, $tmp_t ) );		// タグ重複数
				$rate_c = count( array_intersect( $cats, $tmp_c ) );		// カテゴリ重複数
				$rate_f = count( array_intersect( $features, $tmp_f ) );	// 特徴語重複数
				$tmp_p[] = array(
					'rate' => $rate_t + $rate_c + $rate_f,				// 重複数を合算
					'data' => $p
				);
			}
			usort( $tmp_p, function( $a, $b ) {
				if ( $a['rate'] == $b['rate'] ) {
					return 0;
				}
				return ($a['rate'] > $b['rate']) ? -1 : 1;
			} );

			$related_posts = array();
			foreach ( $tmp_p as $p ) {
				$related_posts[] = $p['data'];
			}
		}
		return $related_posts;
	}

	/* 
	 * タクソノミー（カテゴリー、タグ）から関連ページを取得
	 */
	private function getRelatedPagesByTaxonomy() {
		global $relatedp;
		$related_post_types = $relatedp->getRelatedPostTypes( $this->postId );

		$post_type = get_post_type( $this->postId );
		$tags = $this->getTagIds();
		$cats = wp_get_post_categories( $this->postId );
		$tagIDs = array();
		if ( $tags || $cats ) {
			$args = array(
				'post_type' => $related_post_types,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'oederby' => 'date',
				'post__not_in' => array( $this->postId ),
				'tax_query' => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'category',
						'terms'    => $cats,
						'field'    => 'id',
						'operator' => 'IN'
					),
					array(
						'taxonomy' => 'post_tag',
						'terms'    => $tags,
						'field'    => 'id',
						'operator' => 'IN'
					)
				)
			);
			$related_posts = get_posts( $args );
		} else {
			$related_posts = array();
		}

		/*
		 * カテゴリ、タグの重複数でソートする
		 */
		if ( $related_posts ) {
			$tmp_p = array();
			foreach ( $related_posts as $p ) {
				$tmp_t = $this->getTagIds( $p->ID );
				$tmp_c = wp_get_post_categories( $p->ID );
				$rate_t = count( array_intersect( $tags, $tmp_t ) );	// タグ重複数
				$rate_c = count( array_intersect( $cats, $tmp_c ) );	// カテゴリ重複数
				$tmp_p[] = array(
					'rate' => $rate_t + $rate_c,					// 重複数を合算
					'data' => $p
				);
			}
			usort( $tmp_p, function( $a, $b ) {
				if ( $a['rate'] == $b['rate'] ) {
					return 0;
				}
				return ($a['rate'] > $b['rate']) ? -1 : 1;
			} );

			$related_posts = array();
			foreach ( $tmp_p as $p ) {
				$related_posts[] = $p['data'];
			}
		}

		return $related_posts;
	}


}

?>
