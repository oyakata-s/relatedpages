/*
 * 一括更新をajax実行するスクリプト
 */

/*
 * DOM読み込み時
 */
jQuery(document).ready(function($) {
	/*
	 * アップデートボタン
	 */
	$('#relatedp_update_posts').click(function() {
		$(this).attr('value', updating_dlg);
		$(this).prop('disabled', true);
		$(this).addClass('updating');
		$('#update_posts .update_status').show();
		update_posts();

		return false;
	});

	/*
	 * アップデート実行
	 */
	function update_posts() {
		var query = 'action=relatedp_update';

		$.post(ajaxurl, query, function(data) {
			console.log(data);
			$('#relatedp_update_posts').attr('value', updated_dlg);
			$('#relatedp_update_posts').removeClass('updating');
			$('#update_posts .update_status').html('<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;' + data.total + updated_msg);
		});
	}

});
