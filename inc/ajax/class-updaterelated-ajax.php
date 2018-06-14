<?php
/*
 * 全ページに特徴語を一括更新する
 */
require_once RELATEDP_DIR_PATH . 'inc/base/class-ft-ajax.php';			// ajax用
require_once RELATEDP_DIR_PATH . 'inc/utils/class-textanalysis-utils.php';	// テキスト解析用

if ( ! class_exists( 'UpdateRelatedRunner' ) ) {
class UpdateRelatedRunner extends FtAjaxRunner {

	protected function run() {
		$args = array(
			'post_type' => array('post', 'page'),
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		$all_posts = get_posts( $args );

		$target_cnt = count( $all_posts );
		$add_cnt = 0;
		$upd_cnt = 0;
		$del_cnt = 0;

		global $relatedp;
		$textanalyser = TextAnalysisUtils::getInstance(
			$relatedp->getOption( 'relatedp_yahoo_appid' ),
			$relatedp->getOption( 'relatedp_feature_type' ),
			RELATEDP_CACHE_DIR_PATH,
			RELATEDP_CACHE_EXPIRE
		);

		foreach( $all_posts as $target ) {
			$post_id = $target->ID;
			$features = $textanalyser->getFeatures( $target->post_titlr.','.$target->post_content );

			// 特徴語を削除→追加
			wp_delete_object_term_relationships( $post_id, 'features' );
			wp_set_post_terms( $post_id, $features, 'features', true );
		}

		return array(
			'total' => $target_cnt,
			'add' => $add_cnt,
			'update' => $upd_cnt,
			'delete' => $del_cnt
		);

	}

}
}

?>
