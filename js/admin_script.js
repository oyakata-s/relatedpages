/*
 * 管理画面用スクリプト
 */

jQuery(document).ready(function($) {

	/*
	 * 初期設定
	 */
	var css_code = $('#relatedp_css_custom').val();
	var css = '<style>' + css_code + '</style>';
	var html = outputHtml();
	$('#preview .code').html(esc_html(html));
	$('#preview .output').html(css + html);

	// 抽出方法を変えたら自動設定を無効にする
	// 自動設定をしても保存前の状態で実行されるので
	$('input[name=relatedp_feature_type]').change(function() {
		$('#relatedp_update_posts').prop('disabled', true);
	});

	// 設定変更をプレビューに反映する
	$('#relatedp_css_custom').focusout(function() {
		css_code = $(this).val();
		css = '<style>' + css_code + '</style>';
		changePreview();
	});
	$('#style-set input[type=text], #relatedp_heading_text').focusout(function() {
		changePreview();
	});
	$('#style-set select, #relatedp_number_post').change(function() {
		changePreview();
	});

	// custom css の有効無効切替
	$('#relatedp_use_css').change(function() {
		if ($(this).prop('checked')) {
			$('#relatedp_css_custom')
				.prop('readonly', false)
				.css('opacity', 1);
		} else {
			$('#relatedp_css_custom')
			.prop('readonly', true)
			.css('opacity', 0.5);
		}
	});

	/*
	 * プレビューを変更する
	 */
	function changePreview() {
		container_class = $('#relatedp_container_class').val();
		heading_tag = $('#relatedp_heading_tag').val();
		heading_class = $('#relatedp_heading_class').val();
		heading_text = $('#relatedp_heading_text').val();
		group_class = $('#relatedp_grouping_class').val();
		element_class = $('#relatedp_element_class').val();
		page_cnt = $('#relatedp_number_post').val();

		html = outputHtml();
		$('#preview .code').html(esc_html(html));
		$('#preview .output').html(css + html);
	}

	/*
	 * 設定値からhtmlを出力する
	 */
	function outputHtml() {
		var html = '';
		html += '<div id="related_pages_container" class="' + container_class + '">\n';
		html += '  <' + heading_tag + ' class="related_pages_title ' + heading_class + '">';
		html += heading_text + '</' + heading_tag + '>\n';
		html += '  <div class="related_pages ' + group_class + '">\n';
		for (var i=0; i<page_cnt; i++) {
			html += '    <a class="related_page ' + element_class + '" href="#">\n';
			html += '      <div class="thumbnail" style="background-image:url(' + noimage_url + ');"></div>\n';
			html += '      <p class="related_page_title">Page Title</p>\n';
			html += '    </a>\n';
		}
		html += '  </div>\n';
		html += '</div>\n';
		return html;
	}

	/*
	 * htmlエスケープ
	 */
	function esc_html(string) {
		if(typeof string !== 'string') {
			return string;
		}
		return string.replace(/[&'`"<>]/g, function(match) {
			return {
				'&': '&amp;',
				"'": '&#x27;',
				'`': '&#x60;',
				'"': '&quot;',
				'<': '&lt;',
				'>': '&gt;',
			}[match]
		});
	}
});
