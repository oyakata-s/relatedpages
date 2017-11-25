<?php
/*
 * 特徴語抽出関連
 */

/*
 * 関連ページを取得する
 * アプリケーションIDの有無によって
 * 特徴語による取得、またはタクソノミーによる取得に振り分ける
 */
function get_related_posts( $post_id ) {
	if (RELATEDP_APPID !== false) {
		return get_related_by_feature($post_id);
	} else {
		return get_related_by_taxonomy($post_id);
	}
}

/*
 * テキストからキーフレーズを抽出する
 */
function get_keyphrase_from_text( $text ) {
	// キーフレーズ抽出
	$url = 'https://jlp.yahooapis.jp/KeyphraseService/V1/extract'
		. '?appid=' . RELATEDP_APPID
		. '&output=php'
		. '&sentence=' . rawurlencode($text);
	$data = @file_get_contents($url);
	$data = unserialize($data);

	$features = array();
	foreach($data as $key => $value){
		$features[] = $key;
	}
	$features = array_slice($features, 1, 10);
	return $features;
}

/*
 * コンテンツから特徴語を抽出する
 * キーフレーズ抽出　または　構文解析
 */
function get_feature_from_content( $content ) {
	$text = clean_text($content);

	$features = array();
	if (get_option('relatedp_feature_type') !== 'parse') {
		// キーフレーズ抽出
		$features = get_keyphrase_from_text($text);
		if (count($features) == 0) {	// ひとつも抽出できない場合はテキストが長いから？
			$text = ft_substr($text, 140);
			$features = get_keyphrase_from_text($text);
		}
	} else {
		// 形態素解析
		$url = 'https://jlp.yahooapis.jp/MAService/V1/parse'
			. '?appid=' . RELATEDP_APPID
			. '&results=uniq'
			. '&filter=' . rawurlencode('1|2|3|9')
			. '&sentence=' . rawurlencode($text);
		$parse = simplexml_load_file($url);

		foreach($parse->uniq_result->word_list->word as $value){
			$surface = (string)$value->surface;
			if (!preg_match('/^[0-9０-９,\.]+$/', $surface)) {
				$features[] = $surface;
			}
		}
		$features = array_slice($features, 1, 5);
	}

	return $features;
	// return implode(',', $features);
}

/*
 * 特徴語による関連ページ取得
 * 特徴語＋カテゴリー、タグ
 */
function get_related_by_feature( $post_id ) {
	$related_post_types = get_related_post_types($post_id);
	$post_type = get_post_type($post_id);
	$tags = get_post_tag_ids($post_id);
	$cats = wp_get_post_categories($post_id);
	$features = wp_get_post_terms($post_id, 'features', array('fields'=>'ids'));
	$tagIDs = array();
	if ($tags || $cats || $features) {
		$args = array(
			'post_type' => $related_post_types,	// array('post','tokusan'),array($post_type),
			'post_status' => 'publish',
			'oederby' => 'date',
			'post__not_in' => array($post_id),
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
					'taxonomy' => 'features',
					'terms'    => $features,
					'field'    => 'id',
					'operator' => 'IN'
				)
			)
		);
		$related_posts = get_posts($args);
	} else {
		$related_posts = array();
	}

	/*
	 * 特徴語、カテゴリ、タグの重複数でソートする
	 */
	if ($related_posts) {
		$tmp_p = array();
		foreach ($related_posts as $p) {
			$tmp_t = get_post_tag_ids($p->ID);
			$tmp_c = wp_get_post_categories($p->ID);
			$tmp_f = wp_get_post_terms($p->ID, 'features', array('fields'=>'ids'));
			$rate_t = count(array_intersect($tags, $tmp_t));		// タグ重複数
			$rate_c = count(array_intersect($cats, $tmp_c));		// カテゴリ重複数
			$rate_f = count(array_intersect($features, $tmp_f));	// 特徴語重複数
			$tmp_p[] = array(
				'rate' => $rate_t + $rate_c + $rate_f,				// 重複数を合算
				'data' => $p
			);
		}
		usort($tmp_p, 'rate_sort');
		$related_posts = array();
		foreach ($tmp_p as $p) {
			$related_posts[] = $p['data'];
		}
	}
	return $related_posts;
}

/*
 * タクソノミーによる関連ページ取得
 * カテゴリー、タグ
 */
function get_related_by_taxonomy( $post_id ) {
	$related_post_types = get_related_post_types($post_id);
	$post_type = get_post_type($post_id);
	$tags = get_post_tag_ids($post_id);
	$cats = wp_get_post_categories($post_id);
	$tagIDs = array();
	if ($tags || $cats) {
		$args = array(
			'post_type' => $related_post_types,	// array('post','tokusan'),array($post_type),
			'post_status' => 'publish',
			'oederby' => 'date',
			'post__not_in' => array($post_id),
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
		$related_posts = get_posts($args);
	} else {
		$related_posts = array();
	}

	/*
	 * カテゴリ、タグの重複数でソートする
	 */
	if ($related_posts) {
		$tmp_p = array();
		foreach ($related_posts as $p) {
			$tmp_t = get_post_tag_ids($p->ID);
			$tmp_c = wp_get_post_categories($p->ID);
			$rate_t = count(array_intersect($tags, $tmp_t));	// タグ重複数
			$rate_c = count(array_intersect($cats, $tmp_c));	// カテゴリ重複数
			$tmp_p[] = array(
				'rate' => $rate_t + $rate_c,					// 重複数を合算
				'data' => $p
			);
		}
		usort($tmp_p, 'rate_sort');
		$related_posts = array();
		foreach ($tmp_p as $p) {
			$related_posts[] = $p['data'];
		}
	}
	return $related_posts;
}

/*
 * rateでソート
 */
function rate_sort( $a, $b ) {
	if ($a['rate'] == $b['rate']) {
		return 0;
	}
	return ($a['rate'] > $b['rate']) ? -1 : 1;
}

/*
 * 関連ページとして検索する投稿タイプを取得
 */
function get_related_post_types( $post_id = null ) {
	$related_post_types = array();

	if (get_option('relatedp_sametype_only') && !is_null($post_id)) {
		// 「自分と同じ投稿タイプ」が設定されている場合
		$post_type = get_post_type($post_id);
		$related_post_types[] = $post_type;
	} else {
		// テーマで使用できる投稿タイプを取得
		$post_types = get_theme_post_types();
		foreach ($post_types as $post_type) {
			if (get_option('relatedp_posttype_'.$post_type)) {
				$related_post_types[] = $post_type;
			}
		}
	}

	return $related_post_types;
}

/*
 * post_idの持つすべてのタグIDを取得
 */
function get_post_tag_ids( $post_id ) {
	$tags = wp_get_post_tags($post_id);
	$tagIDs = array();
	foreach( $tags as $tag ){
		$tagIDs[] = $tag->term_id;
	}
	return $tagIDs;
}

?>
