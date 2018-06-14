<?php
/*
 * 関連ページ設定画面用
 */
?>
<div id="relatedp-setting" class="wrap">
	<h2><?php _e('Related Pages Setting', 'relatedpages'); ?></h2>
	<form method="POST" action="options.php">
<?php
		global $relatedp;
		$app_id = $relatedp->getOption( 'relatedp_yahoo_appid' );
		settings_fields( 'relatedp_settings_group' );
		do_settings_sections( 'relatedp_settings_group' );
?>

		<!-- tab control -->
		<ul id="settings-tab">
			<li class="active"><a href="#"><?php _e( 'Text Analysis Setup', 'relatedpages' ); ?></a></li>
			<li><a href="#"><?php _e( 'Basic Settings', 'relatedpages' ); ?></a></li>
			<li><a href="#"><?php _e( 'Style Settings', 'relatedpages' ); ?></a></li>
			<li><a href="#"><?php _e( 'Shortcode', 'relatedpages' ); ?></a></li>
		</ul>

		<div id="tab-contents">

		<!-- setup -->
		<table class="form-table tab-content active">
			<tr>
				<th scope="row"><label for="relatedp_yahoo_appid">アプリケーションID</label></th>
				<td><fieldset>
					<input type="text" name="relatedp_yahoo_appid" id="relatedp_yahoo_appid" placeholder="アプリケーションIDを入力してください。" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_yahoo_appid' ) ); ?>">
					<p class="description"><a href="https://developer.yahoo.co.jp/" target="_blank">Yahoo!デベロッパーネットワーク</a>で<a href="https://e.developer.yahoo.co.jp/register" target="_blank">アプリケーションIDを取得</a>してください。<br>アプリケーションIDを入力することで、特徴語による関連ページを取得できるようになります。</p>
					</fieldset>
				</td>
			</tr>
		<?php if ( $app_id ) : ?>
			<tr>
				<th scope="row">
					特徴語抽出
				</th>
				<td><fieldset id="feature_type">
					<input type="radio" name="relatedp_feature_type" id="relatedp_keyphrase" value="keyphrase" <?php checked( $relatedp->getOption( 'relatedp_feature_type' ) === 'keyphrase', 1 ); ?> /><label for="relatedp_keyphrase">キーフレーズ</label>&emsp;
					<input type="radio" name="relatedp_feature_type" id="relatedp_parse" value="parse" <?php checked( $relatedp->getOption( 'relatedp_feature_type' ) !== 'keyphrase', 1 ); ?> /><label for="relatedp_parse">形態素解析</label>
					<p class="keyphrase description">テキストからキーフレーズを抽出します。</p>
					<p class="parse description">形態素解析を行い、頻出する語句を抽出します。</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">自動設定</th>
				<td><fieldset id="update_posts">
					<input type="button" class="button-primary" name="relatedp_update_posts" id="relatedp_update_posts" value="自動設定する" <?php echo ( $app_id !== false ) ? '' : 'disabled'; ?> />&emsp;
					<span class="update_status"><i class="fa fa-refresh fa-spin"></i>&nbsp;しばらくお待ちください…</span>
					<p class="description">
						すべての既存ページに特徴語を設定します。ページ数によって時間がかかることがあります。
					</p>
					</fieldset>
				</td>
			</tr>
		<?php endif; ?>
		</table>

		<!-- basic setting -->
		<table class="form-table tab-content">
			<tr>
				<th scope="row"><label for="relatedp_post_type"><?php _e( 'Post Type', 'relatedpages' ); ?></label></th>
				<td><fieldset>
				<?php
					$post_types = FtUtils::getSuppotedPostTypes();
					foreach ( $post_types as $post_type ) :
						$checkbox_name = 'relatedp_posttype_' . $post_type;
						$checkbox_status = esc_attr( $relatedp->getOption( $checkbox_name ) );
				?>
					<input type="checkbox" name="<?php echo $checkbox_name; ?>" id="<?php echo $checkbox_name; ?>" <?php checked( $checkbox_status === 'on', 1 );?> />
					<label for="<?php echo $checkbox_name; ?>"><?php echo $post_type; ?></label>&emsp;
				<?php
					endforeach;
				?>
					<p class="description"><?php echo _e( 'Type related post where is displayed', 'relatedpages' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="relatedp_sametype_only"><?php _e( 'Only the same type', 'relatedpages' ); ?></label></th>
				<td><fieldset>
					<input type="checkbox" name="relatedp_sametype_only" id="relatedp_sametype_only" <?php checked( $relatedp->getOption( 'relatedp_sametype_only' ) === 'on', 1 ); ?> />
					<label for="relatedp_sametype_only"><?php echo _e(' Show only the same type', 'relatedpages' ); ?></label><br />
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="relatedp_default_img"><?php _e( 'Default Image URL', 'relatedpages' ); ?></label>
					<p class="description"><?php _e( 'Default image in case there is no image in the post.', 'relatedpages' ); ?></p>
				</th>
				<td><fieldset id="media-upload">
					<input name="relatedp_default_img" type="text" value="<?php echo esc_html( $relatedp->getOption( 'relatedp_default_img' ) ); ?>" />
					<div class="btn-control">
						<input class="button-primary" type="button" name="relatedp_media" value="選択" />
						<input class="button-secondary" type="button" name="relatedp_media-clear" value="クリア" />
					</div>
					<div id="media">
						<img src="<?php echo esc_html( $relatedp->getOption( 'relatedp_default_img' ) ); ?>" />
					</div>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Automatically add', 'relatedpages' ); ?></th>
				<td><fieldset>
					<input type="checkbox" name="relatedp_auto_add" id="relatedp_auto_add" value="1" <?php checked( $relatedp->getOption( 'relatedp_auto_add' ), 1 ); ?> />
					<label for="relatedp_auto_add"><span><?php _e( 'Automatically add to the_content', 'relatedpages' ); ?></span></label>
					</fieldset>
				</td>
			</tr>
		</table>

		<!-- style settings -->
		<table class="form-table tab-content">
			<tr>
				<th scope="row"><label for="relatedp_heading_text"><?php _e( 'Heading Text', 'relatedpages' ); ?></label></th>
				<td><fieldset>
					<input type="text" name="relatedp_heading_text" id="relatedp_heading_text" placeholder="<?php _e( 'Heading Text', 'relatedpages' ); ?>" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_heading_text' ) ); ?>">
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="relatedp_number_post"><?php _e( 'Number of similar Post to display', 'relatedpages' ); ?></label></th>
				<td><fieldset>
					<select name="relatedp_number_post" id="relatedp_number_post">
					<?php
						$selected = esc_attr( $relatedp->getOption( 'relatedp_number_post' ) );
						for ( $i=2; $i<10; $i+=2 ) {
							if ($i == $selected) {
								echo '<option value="' . $i . '" selected>' . $i . '</option>';
							} else {
								echo '<option value="' . $i . '">' . $i . '</option>';
							}
						}
					?>
					</select>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Set Class/Tag', 'relatedpages' ); ?></th>
				<td><fieldset id="style-set">
					<label for="relatedp_container_class"><?php _e( 'Container Class', 'relatedpages' ); ?></label>
					<input type="text" name="relatedp_container_class" id="relatedp_container_class" placeholder="<?php _e( 'Container Class', 'relatedpages' ); ?>" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_container_class' ) ); ?>"><br />
					<label for="relatedp_heading_tag"><?php _e( 'Heading Tag', 'relatedpages' ); ?></label>
					<select name="relatedp_heading_tag" id="relatedp_heading_tag">
					<?php
						$selected = esc_attr( $relatedp->getOption( 'relatedp_heading_tag' ) );
						for ($i=1; $i<7; $i++) {
							$tag = 'h' . $i;
							if ($tag == $selected) {
								echo '<option value="' . $tag . '" selected>' . $tag . '</option>';
							} else {
								echo '<option value="' . $tag . '">' . $tag . '</option>';
							}
						}
					?>
					</select><br />
					<label for="relatedp_heading_class"><?php _e( 'Heading Class', 'relatedpages' ); ?></label>
					<input type="text" name="relatedp_heading_class" id="relatedp_heading_class" placeholder="<?php _e( 'Heading Class', 'relatedpages' ); ?>" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_heading_class' ) ); ?>"><br />
					<label for="relatedp_grouping_class"><?php _e( 'Grouping Class', 'relatedpages' ); ?></label>
					<input type="text" name="relatedp_grouping_class" id="relatedp_grouping_class" placeholder="<?php _e( 'Grouping Class', 'relatedpages' ); ?>" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_grouping_class' ) ); ?>"><br />
					<label for="relatedp_element_class"><?php _e( 'Element Class', 'relatedpages' ); ?></label>
					<input type="text" name="relatedp_element_class" id="relatedp_element_class" placeholder="<?php _e( 'Element Class', 'relatedpages' ); ?>" value="<?php echo esc_attr( $relatedp->getOption( 'relatedp_element_class' ) ); ?>"><br />
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="relatedp_css_custom"><?php _e( 'CSS Custom', 'relatedpages' ); ?></label>
					<p class="description"><?php _e( 'Edit CSS directly', 'relatedpages' ); ?></p>
				</th>
				<td><fieldset id="use_custom_css">
					<input type="checkbox" name="relatedp_use_css" id="relatedp_use_css" <?php checked( $relatedp->getOption( 'relatedp_use_css' ) === 'on', 1 ); ?> />
					<label for="relatedp_use_css"><?php echo _e( 'Use following css', 'relatedpages' ); ?></label><br />
					<?php
						$css = $relatedp->getOption( 'relatedp_css_custom' );
						if ( !$css ) {
							if ( WP_Filesystem() ) {
								global $wp_filesystem;
								$css = $wp_filesystem->get_contents( RELATEDP_DIR_PATH . 'css/style.css' );
							}
						}
					?>
					<textarea name="relatedp_css_custom" id="relatedp_css_custom" placeholder="<?php echo _e( 'Plsease enter custom css code here.', 'relatedpages' ) ?>" rows="10"><?php echo esc_attr( $css ); ?></textarea>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e('Preview', 'relatedpages'); ?>
				</th>
				<td id="preview">
					<div class="output"></div>
<!-- 					<h4><?php _e('Output HTML', 'relatedpages'); ?></h4>
					<div class="source">
						<pre><code class="code"></code></pre>
					</div> -->
				</td>
			</tr>
		</table>

		<!-- shortcode -->
		<table class="form-table tab-content">

			<tr>
				<th scope="row"><?php _e('Shortcode', 'relatedpages'); ?></th>
				<td>
					<p class="description">
						<code>[relatedpages]</code>
						<table class="shortcode_table">
							<tr>
								<th>Parameter(Optical)</th>
								<th>Type</th>
								<th>Default</th>
								<th>Description</th>
							</tr>
							<tr>
								<td>id</td>
								<td>number</td>
								<td>current post id</td>
								<td>Specify the post ID.</td>
							</tr>
						</table>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Template Tag', 'relatedpages'); ?></th>
				<td>
					<p class="description">
						<code><?php echo esc_html("<?php if ( function_exists( 'the_relatedpages' ) ) { the_relatedpages(); } ?>"); ?></code>
						<table class="shortcode_table">
							<tr>
								<th>Parameter(Optical)</th>
								<th>Type</th>
								<th>Default</th>
								<th>Description</th>
							</tr>
							<tr>
								<td>id</td>
								<td>number</td>
								<td>current post id</td>
								<td>Specify the post ID.</td>
							</tr>
						</table>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

		</div>
		<!-- /.tab-content -->

	</form>
</div>
