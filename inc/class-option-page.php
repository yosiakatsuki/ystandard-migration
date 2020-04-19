<?php
/**
 * 設定ページ
 *
 * @package yStandard_migration
 * @author  yosiakatsuki
 * @license GPL-2.0+
 */

namespace ystandard_migration;

defined( 'ABSPATH' ) || die();

/**
 * Class Option_Page
 *
 * @package ystandard_migration
 */
class Option_Page {

	/**
	 * Nonce Action.
	 */
	const NONCE_ACTION = 'ystandard_migration';
	/**
	 * Nonce Name.
	 */
	const NONCE_NAME = 'ystandard_migration_nonce';

	/**
	 * 件数
	 *
	 * @var int
	 */
	private $success_count = 0;

	/**
	 * Option_Page constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * 設定ページ追加
	 */
	public function add_admin_menu() {
		add_menu_page(
			'[ys]設定移行',
			'[ys]設定移行',
			'manage_options',
			'ystd-migration',
			[ $this, 'migration' ],
			'',
			4
		);
	}

	/**
	 * Enqueue
	 *
	 * @param string $hook_suffix suffix.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_ystd-migration' === $hook_suffix ) {
			wp_enqueue_style(
				'ys-migration',
				YSTDMG_URL . '/css/migration.css',
				[],
				filemtime( YSTDMG_PATH . '/css/migration.css' )
			);
		}
	}

	/**
	 * 設定移行ページ
	 */
	public function migration() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( $this->migration_options() ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo $this->success_count; ?>件 設定を更新しました。</p>
			</div>
			<?php
		}
		?>
		<div class="wrap">
			<h2>設定移行(v3 -> v4)</h2>
			<p>yStandard v3からv4への設定移行ツール</p>
			<p><strong>※変換したデータは元に戻せません。必ずバックアップを作成してから実行してください。</strong></p>
			<form method="post" action="" id="ys-migration">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<div class="ys-migration-section">
					<h3>ページテンプレート</h3>
					<p>
						「タイトルなし（スリム・ワイド）」テンプレートをv4用のテンプレート「投稿ヘッダーなし 1カラム」へ変換します。<br>
						<small>※手動で変更する場合は「編集」リンクから編集を実施してください。</small>
					</p>
					<?php if ( $this->get_page_template() ) : ?>
						<div class="ys-migration-section__button">
							<button class="button button-primary" type="submit" name="migration" value="template">
								ページテンプレート設定を変換
							</button>
						</div>
					<?php endif; ?>
				</div>
				<div class="ys-migration-section">
					<h3>簡易人気記事ランキング用設定</h3>
					<p>各記事に作成されたランキング作成用の設定値を削除します。</p>
					<?php if ( $this->get_ranking() ) : ?>
						<div class="ys-migration-section__button">
							<button class="button button-primary" type="submit" name="migration" value="ranking">
								簡易人気記事ランキング用設定を変換
							</button>
						</div>
					<?php endif; ?>
				</div>
				<div class="ys-migration-section">
					<h3>その他投稿設定</h3>
					<p>「AMPページを作成しない」「フォローボックスを表示しない」設定を削除します</p>
					<?php if ( $this->get_other_post_meta() ) : ?>
						<div class="ys-migration-section__button">
							<button class="button button-primary" type="submit" name="migration" value="post_meta">
								その他投稿設定を変換
							</button>
						</div>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<?php

	}

	/**
	 * ページテンプレートの変換内容取得
	 *
	 * @return bool
	 */
	private function get_page_template() {
		$post_meta = new Post_Meta();
		$button    = true;
		$row       = '';
		$data      = $post_meta->search_page_template();
		if ( $data ) {
			foreach ( $data as $post ) {
				$row .= $this->get_post_row( $post, true );
			}
		} else {
			$button = false;
			$row    = $this->get_row( '対象データはありません。' );
		}

		$this->print_list( $row );

		return $button;
	}

	/**
	 * ランキング
	 *
	 * @return bool
	 */
	private function get_ranking() {
		$button    = true;
		$post_meta = new Post_Meta();
		$data      = $post_meta->search_ranking_data();
		$row       = '';
		if ( ! empty( $data ) ) {
			$row = $this->get_row( "対象件数: ${data}件" );
		} else {
			$button = false;
			$row    = $this->get_row( '対象データはありません。' );
		}
		$this->print_list( $row );

		return $button;
	}

	/**
	 * その他投稿設定
	 *
	 * @return bool
	 */
	private function get_other_post_meta() {
		$button    = true;
		$post_meta = new Post_Meta();
		$data      = $post_meta->search_other_post_meta();
		$row       = '';
		if ( ! empty( $data ) ) {
			$row = $this->get_row( "対象件数: ${data}件" );
		} else {
			$button = false;
			$row    = $this->get_row( '対象データはありません。' );
		}
		$this->print_list( $row );

		return $button;
	}


	/**
	 * 対象の投稿を表示するテンプレート
	 *
	 * @param \WP_Post $post Post.
	 */
	private function get_post_row( $post, $show_edit = false ) {
		$title = sprintf(
			'<a class="ys-migration-post__title" href="%s" target="_blank">%s</a>',
			get_permalink( $post ),
			$post->post_title
		);
		$edit  = '';
		if ( $show_edit ) {
			$edit = sprintf(
				'<a class="ys-migration-post__edit" href="%s" target="_blank">編集</a>',
				get_edit_post_link( $post )
			);
		}

		return $this->get_row( $title . $edit );
	}

	/**
	 * 一覧表示
	 *
	 * @param string $row Row.
	 */
	private function print_list( $row ) {
		printf(
			'<ul class="ys-migration-post__list">%s</ul>',
			$row
		);
	}

	/**
	 * 行作成
	 *
	 * @param string $text text.
	 *
	 * @return string
	 */
	private function get_row( $text ) {
		return sprintf(
			'<li class="ys-migration-post__row">%s</li>',
			$text
		);
	}

	/**
	 * 設定更新
	 */
	private function migration_options() {
		if ( ! self::verify_nonce( self::NONCE_NAME, self::NONCE_ACTION ) ) {
			return false;
		}
		if ( ! isset( $_POST['migration'] ) ) {
			return false;
		}
		$this->success_count = 0;
		$post_meta           = new Post_Meta();
		// テンプレート設定.
		if ( 'template' === $_POST['migration'] ) {
			$this->success_count = $post_meta->update_template();
		}
		// ランキングデータ.
		if ( 'ranking' === $_POST['migration'] ) {
			$this->success_count = $post_meta->delete_ranking();
		}
		// その他.
		if ( 'post_meta' === $_POST['migration'] ) {
			$this->success_count = $post_meta->delete_other_post_meta();
		}

		if ( empty( $this->success_count ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Nonceチェック
	 *
	 * @param string $name   Name.
	 * @param string $action Action.
	 *
	 * @return bool|int
	 */
	public static function verify_nonce( $name, $action ) {
		// nonceがセットされているかどうか確認.
		if ( ! isset( $_POST[ $name ] ) ) {
			return false;
		}

		return wp_verify_nonce( $_POST[ $name ], $action );
	}

}

new Option_Page();
