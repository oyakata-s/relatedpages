<?php
/*
 * 全ページに特徴語を一括更新する関数
 * ajax処理の為、JSON形式で結果を出力
 */

function relatedp_update() {
	$args = array(
		'post_type' => array('post', 'page'),
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$all_posts = get_posts($args);

	$target_cnt = count($all_posts);
	$add_cnt = 0;
	$upd_cnt = 0;
	$del_cnt = 0;

	foreach($all_posts as $target) {
		$post_id = $target->ID;
		$features = get_feature_from_content($target->post_titlr.','.$target->post_content);

		// 特徴語を削除→追加
		wp_delete_object_term_relationships($post_id, 'features');
		wp_set_post_terms($post_id, $features, 'features', true);
	}

	/*
	 * 結果をJSONで返す
	 */
	// header定義
	header('Content-Type:application/json;charset=utf-8');
	echo json_encode(array(
		'total' => $target_cnt,
		'add' => $add_cnt,
		'update' => $upd_cnt,
		'delete' => $del_cnt)
	);

	die();
}

?>
