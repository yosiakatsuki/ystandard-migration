<?php
/**
 * サイト設定
 *
 * @package yStandard_migration
 * @author  yosiakatsuki
 * @license GPL-2.0+
 */

namespace ystandard_migration;

defined( 'ABSPATH' ) || die();

/**
 * Class Site_Option
 *
 * @package ystandard_migration
 */
class Site_Option {

	/**
	 * 削除する設定リスト
	 */
	const DELETE_OPTIONS = [
		'ys_show_archive_author',
		'ys_admin_enable_tiny_mce_style',
		'ys_front_page_layout',
		'ys_admin_enable_tiny_mce_style',
		'ys_admin_enable_block_editor_style',
		'ys_customizer_section_disable_ys_color',
		'ys_query_cache_ranking',
		'ys_show_search_form_on_slide_menu',
		'ys_show_post_thumbnail',
		'ys_show_page_thumbnail',
		'ys_show_post_follow_box',
		'ys_design_one_col_thumbnail_type',
		'ys_design_one_col_content_type',
		'ys_show_post_before_content_widget',
		'ys_post_before_content_widget_priority',
		'ys_show_post_after_content_widget',
		'ys_post_after_content_widget_priority',
		'ys_show_page_before_content_widget',
		'ys_page_before_content_widget_priority',
		'ys_show_page_after_content_widget',
		'ys_page_after_content_widget_priority',
		'ys_show_page_follow_box',
		'ys_wp_header_media_full',
		'ys_wp_header_media_full_type',
		'ys_wp_header_media_full_opacity',
		'ys_wp_header_media_all_page',
		'ys_sns_share_button_feedly',
		'ys_sns_share_button_rss',
		'ys_sns_share_col_sp',
		'ys_sns_share_col_tablet',
		'ys_sns_share_col_pc',
		'ys_sns_share_on_entry_header',
		'ys_sns_share_on_below_entry',
		'ys_follow_url_twitter',
		'ys_follow_url_facebook',
		'ys_follow_url_instagram',
		'ys_follow_url_tumblr',
		'ys_follow_url_youtube',
		'ys_follow_url_github',
		'ys_follow_url_pinterest',
		'ys_follow_url_linkedin',
		'ys_follow_url_amazon',
		'ys_ga_tracking_id_amp',
		'ys_amp_enable',
		'ys_show_amp_before_content_widget',
		'ys_amp_before_content_widget_priority',
		'ys_show_amp_after_content_widget',
		'ys_amp_after_content_widget_priority',
		'ys_amp_thumbnail_type',
		'ys_front_page_type',
		'ys_ogp_fb_admins',
		'ys_subscribe_col_sp',
		'ys_subscribe_col_pc',
		'ys_query_cache_ys_options',
		'ys_enqueue_gutenberg_css',
	];


	/**
	 * サイト設定の変換リスト
	 *
	 * @return array
	 */
	public function search_convert() {
		$option = [];
		// SNSシェアボタン.
		$data = get_option( 'ys_sns_share_on_entry_header', null );
		if ( ! is_null( $data ) ) {
			$option[] = 'SNSシェアボタン表示設定(記事上)';
		}
		if ( ! is_null( $data ) ) {
			$option[] = 'SNSシェアボタン表示設定(記事下)';
		}
		$data = get_option( 'ys_show_search_form_on_slide_menu', null );
		if ( ! is_null( $data ) ) {
			$option[] = 'モバイルメニュー用検索フォーム表示設定';
		}
		$data = get_option( 'ys_show_sidebar_mobile', null );
		if ( ! is_null( $data ) ) {
			$option[] = 'モバイル表示でサイドバーを非表示にする設定';
		}
		$data = get_option( 'ys_show_post_publish_date', null );
		if ( 'both' !== $data && 'publish' !== $data && 'update' !== $data && 'none' !== $data ) {
			$option[] = '投稿日時表示(投稿)';
		}
		$data = get_option( 'ys_show_page_publish_date', null );
		if ( 'both' !== $data && 'publish' !== $data && 'update' !== $data && 'none' !== $data ) {
			$option[] = '投稿日時表示(固定ページ)';
		}
		$data = get_option( 'ys_archive_type', null );
		if (  is_null( $data ) ) {
			$option[] = 'アーカイブレイアウト設定';
		}

		return $option;
	}

	/**
	 * サイトの設定変換
	 *
	 * @return int
	 */
	public function convert_options() {
		$count = 0;
		// SNSシェアボタン.
		$data = get_option( 'ys_sns_share_on_entry_header', null );
		if ( is_null( $data ) || 1 === $data || '1' === $data ) {
			$count += update_option( 'ys_share_button_type_header', 'circle' ) ? 1 : 0;
		} else {
			$count += update_option( 'ys_share_button_type_header', 'none' ) ? 1 : 0;
		}
		delete_option( 'ys_sns_share_on_entry_header' );
		$data = get_option( 'ys_sns_share_on_below_entry', null );
		if ( is_null( $data ) || 1 === $data || '1' === $data ) {
			$count += update_option( 'ys_share_button_type_footer', 'circle' ) ? 1 : 0;
		} else {
			$count += update_option( 'ys_share_button_type_footer', 'none' ) ? 1 : 0;
		}
		delete_option( 'ys_sns_share_on_below_entry' );
		// モバイルメニュー用検索フォーム.
		$data = get_option( 'ys_show_search_form_on_slide_menu', null );
		delete_option( 'ys_show_search_form_on_slide_menu' );
		$count += update_option( 'ys_show_header_search_form', $data ) ? 1 : 0;
		// モバイルでサイドバーを非表示にする.
		$data = get_option( 'ys_show_sidebar_mobile', null );
		delete_option( 'ys_show_sidebar_mobile' );
		$count += update_option( 'ys_hide_sidebar_mobile', $data ) ? 1 : 0;
		// 投稿日付.
		$data = get_option( 'ys_show_post_publish_date', null );
		if ( is_null( $data ) || 1 === $data || '1' === $data ) {
			$count += update_option( 'ys_show_post_publish_date', 'both' ) ? 1 : 0;
		} elseif ( 'both' !== $data && 'publish' !== $data && 'update' !== $data ) {
			$count += update_option( 'ys_show_post_publish_date', 'none' ) ? 1 : 0;
		}
		$data = get_option( 'ys_show_page_publish_date', null );
		if ( is_null( $data ) || 1 === $data || '1' === $data ) {
			$count += update_option( 'ys_show_page_publish_date', 'both' ) ? 1 : 0;
		} elseif ( 'both' !== $data && 'publish' !== $data && 'update' !== $data )  {
			$count += update_option( 'ys_show_page_publish_date', 'none' ) ? 1 : 0;
		}
		// アーカイブレイアウト.
		$data = get_option( 'ys_archive_type', null );
		if ( is_null( $data ) ) {
			$count += update_option( 'ys_archive_type', 'list' ) ? 1 : 0;
		}

		return $count;
	}

	/**
	 * サイト設定の変換リスト
	 *
	 * @return int
	 */
	public function search_delete() {
		$count = 0;
		foreach ( self::DELETE_OPTIONS as $item ) {
			if ( ! is_null( get_option( $item, null ) ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * 設定の削除
	 *
	 * @return int
	 */
	public function delete_options() {
		$count = 0;
		foreach ( self::DELETE_OPTIONS as $item ) {
			$count += delete_option( $item ) ? 1 : 0;
		}

		return $count;
	}
}

