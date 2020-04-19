<?php
/**
 * ショートコード関連
 *
 * @package yStandard_migration
 * @author  yosiakatsuki
 * @license GPL-2.0+
 */

namespace ystandard_migration;

defined( 'ABSPATH' ) || die();

/**
 * Class Short_Code
 *
 * @package ystandard_migration
 */
class Short_Code {

	/**
	 * 廃止されるショートコード
	 */
	const SHORT_CODES = [
		'ys_author_list',
		'ys_post_ranking',
		'ys_tax_posts',
		'ys_get_posts',
		'ys_text',
		'ys_post_paging',
		'ys_post_tax',
	];

	/**
	 * 廃止されるショートコードを使っている投稿を検索
	 *
	 * @return array
	 */
	public function search_short_code() {

		$result = [];
		foreach ( self::SHORT_CODES as $short_code ) {
			$args  = [
				'posts_per_page' => - 1,
				'post_type'      => 'any',
				's'              => $short_code,
			];
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					$result[] = [
						'post'       => $post,
						'short_code' => $short_code,
					];
				}
			}
		}

		return $result;
	}
}
