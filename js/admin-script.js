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
	// $('#preview .code').html(esc_html(html));
	$('#preview .output').html(css + html);

	/*
	 * タブ切替
	 */
	$('#settings-tab li').on('click', 'a', function() {
		var index = $('#settings-tab li a').index(this);

		$('#settings-tab li').each(function() {
			$(this).removeClass('active');
		});
		$('#tab-contents .tab-content').each(function() {
			$(this).removeClass('active');
		});

		$(this).parent().addClass('active');
		$('#tab-contents .tab-content').eq(index).addClass('active')

		return false;
	});

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
		relatedp_update.container_class = $('#relatedp_container_class').val();
		relatedp_update.heading_tag = $('#relatedp_heading_tag').val();
		relatedp_update.heading_class = $('#relatedp_heading_class').val();
		relatedp_update.heading_text = $('#relatedp_heading_text').val();
		relatedp_update.group_class = $('#relatedp_grouping_class').val();
		relatedp_update.element_class = $('#relatedp_element_class').val();
		relatedp_update.page_cnt = $('#relatedp_number_post').val();

		html = outputHtml();
		// $('#preview .code').html(esc_html(html));
		$('#preview .output').html(css + html);
	}

	/*
	 * 設定値からhtmlを出力する
	 */
	function outputHtml() {
		var html = '';
		html += '<div id="related_pages_container" class="' + relatedp_update.container_class + '">\n';
		html += '  <' + relatedp_update.heading_tag + ' class="' + relatedp_update.heading_class + '">';
		html += relatedp_update.heading_text + '</' + relatedp_update.heading_tag + '>\n';
		html += '  <div class="' + relatedp_update.group_class + '">\n';
		for (var i=0; i<relatedp_update.page_cnt; i++) {
			html += '    <a class="post ' + relatedp_update.element_class + '" href="#">\n';
			html += '      <figure class="post-thumbnail" style="background-image:url(' + relatedp_update.noimage_url + ');"></figure>\n';
			html += '      <p class="post-title">Page Title</p>\n';
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
