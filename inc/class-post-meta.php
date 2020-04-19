<?php
/**
 * Post Meta 移行
 *
 * @package yStandard_migration
 * @author  yosiakatsuki
 * @license GPL-2.0+
 */

namespace ystandard_migration;

defined( 'ABSPATH' ) || die();

/**
 * Class Post_Meta
 *
 * @package ystandard_migration
 */
class Post_Meta {

	/**
	 * ページテンプレートのmetaキー
	 */
	const KEY_PAGE_TEMPLATE = '_wp_page_template';

	/**
	 * ページテンプレートの変換リスト
	 */
	const MIGRATION_PAGE_TEMPLATE = [
		'page-template/template-one-column-no-title.php'      => 'page-template/template-blank-wide.php',
		'page-template/template-one-column-no-title-slim.php' => 'page-template/template-blank.php',
	];

	/**
	 * 変換が必要なテンプレート検索
	 *
	 * @return bool|\WP_Post[]
	 */
	public function search_page_template() {
		$meta_query = [];
		foreach ( self::MIGRATION_PAGE_TEMPLATE as $key => $value ) {
			$meta_query[] = [
				'key'     => self::KEY_PAGE_TEMPLATE,
				'value'   => $key,
				'compare' => '=',
			];
		}
		$meta_query['relation'] = 'OR';
		$args                   = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_query'     => $meta_query,
		];
		$posts                  = get_posts( $args );
		if ( $posts ) {
			return $posts;
		}

		return false;
	}


	/**
	 * ランキングデータの検索
	 *
	 * @return bool|\WP_Post[]
	 */
	public function search_ranking_data() {
		$args  = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_key'       => 'ys_pv_all',
			'meta_compare'   => 'EXISTS',
		];
		$posts = get_posts( $args );

		return count( $posts );
	}


	/**
	 * IDのリストを作成
	 *
	 * @param \WP_Post[] $posts Posts.
	 * @param array      $merge Merge.
	 *
	 * @return array
	 */
	private function get_id_list( $posts, $merge = [] ) {
		$list = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$list[] = $post->ID;
			}
		}

		return array_merge(
			$merge,
			$list
		);
	}

	/**
	 * その他投稿設定データの検索
	 *
	 * @return bool|\WP_Post[]
	 */
	public function search_other_post_meta() {
		/**
		 * OR検索やたら遅いから分割
		 */
		$count = 0;
		// [ys_post_meta_amp_desable].
		$args  = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_key'       => 'ys_post_meta_amp_desable',
			'meta_compare'   => 'EXISTS',
		];
		$posts = get_posts( $args );
		$list  = $this->get_id_list( $posts );
		// [ys_hide_follow].
		$args['meta_key']     = 'ys_hide_follow';
		$args['post__not_in'] = $list;
		$posts                = get_posts( $args );
		$list                 = $this->get_id_list( $posts, $list );

		return count( $list );
	}

	/**
	 * テンプレート設定の更新
	 */
	public function update_template() {
		$count = 0;
		foreach ( self::MIGRATION_PAGE_TEMPLATE as $key => $value ) {
			$args  = [
				'posts_per_page' => - 1,
				'post_type'      => 'any',
				'meta_key'       => self::KEY_PAGE_TEMPLATE,
				'meta_value'     => $key,
				'meta_compare'   => '=',
			];
			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$result = update_post_meta(
					$post->ID,
					self::KEY_PAGE_TEMPLATE,
					self::MIGRATION_PAGE_TEMPLATE[ $key ]
				);
				$count  += empty( $result ) ? 0 : 1;
			}
		}

		return $count;
	}

	/**
	 * 簡易ランキング設定の削除
	 *
	 * @return int
	 */
	public function delete_ranking() {
		$count = 0;
		$args  = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_key'       => 'ys_pv_all',
			'meta_compare'   => 'EXISTS',
		];
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			delete_post_meta( $post->ID, 'ys_pv_key_d' );
			delete_post_meta( $post->ID, 'ys_pv_val_d' );
			delete_post_meta( $post->ID, 'ys_pv_key_w' );
			delete_post_meta( $post->ID, 'ys_pv_val_w' );
			delete_post_meta( $post->ID, 'ys_pv_key_m' );
			delete_post_meta( $post->ID, 'ys_pv_val_m' );
			$result = delete_post_meta( $post->ID, 'ys_pv_all' );
			$count += empty( $result ) ? 0 : 1;
		}

		return $count;
	}
	/**
	 * 簡易ランキング設定の削除
	 *
	 * @return int
	 */
	public function delete_other_post_meta() {
		$count = 0;
		$args  = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_key'       => 'ys_post_meta_amp_desable',
			'meta_compare'   => 'EXISTS',
		];
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$result = delete_post_meta( $post->ID, 'ys_post_meta_amp_desable' );
			$count += empty( $result ) ? 0 : 1;
		}
		$args  = [
			'posts_per_page' => - 1,
			'post_type'      => 'any',
			'meta_key'       => 'ys_hide_follow',
			'meta_compare'   => 'EXISTS',
		];
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$result = delete_post_meta( $post->ID, 'ys_hide_follow' );
			$count += empty( $result ) ? 0 : 1;
		}

		return $count;
	}
}
