<?php
/**
 * 投稿記事一覧（非同期ページネーション付き）
 *
 * 固定ページにショートコード [pagenation] を記述すると、投稿記事一覧を10件ずつ表示します。
 * 設置方法は readme.txt を参照してください。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_PAGENATION_PER_PAGE', 10 );    // 1ページあたりの表示件数
define( 'WP_PAGENATION_BODY_LENGTH', 80 ); // 本文の最大表示文字数
define( 'WP_PAGENATION_ACTION', 'wp_pagenation_fetch' );
define( 'WP_PAGENATION_THUMB_SIZE', 'medium' );             // サムネイルの画像サイズ
define( 'WP_PAGENATION_DEFAULT_THUMB', 'default_thumb.jpg' ); // サムネイル未設定時の画像

/**
 * 指定ページ分の投稿記事を取得する
 *
 * @param int $page ページ番号（1始まり）
 * @return array { page, totalPages, items[] }
 */
function wp_pagenation_get_posts( $page ) {
	$page  = max( 1, (int) $page );
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => WP_PAGENATION_PER_PAGE,
			'paged'               => $page,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'has_password'        => false,
			'ignore_sticky_posts' => true,
		)
	);

	$items = array();
	foreach ( $query->posts as $post ) {
		$body    = wp_pagenation_get_body( $post->post_content );
		$items[] = array(
			'date'     => get_the_date( 'Y年m月d日', $post ),
			'datetime' => get_the_date( 'Y-m-d', $post ),
			'title'    => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'thumb'    => wp_pagenation_get_thumb( $post ),
			'body'     => $body['text'],
			'more'     => $body['more'],
			'url'      => get_permalink( $post ),
		);
	}

	return array(
		'page'       => $page,
		'totalPages' => max( 1, (int) $query->max_num_pages ),
		'items'      => $items,
	);
}

/**
 * 投稿のサムネイル（アイキャッチ画像）のURLを取得する
 *
 * 未設定の場合は wp-pagenation フォルダ内のサンプル画像を返す。
 *
 * @param WP_Post $post 投稿
 * @return string 画像のURL
 */
function wp_pagenation_get_thumb( $post ) {
	$url = get_the_post_thumbnail_url( $post, WP_PAGENATION_THUMB_SIZE );
	if ( ! $url ) {
		$url = get_stylesheet_directory_uri() . '/wp-pagenation/' . WP_PAGENATION_DEFAULT_THUMB;
	}
	return $url;
}

/**
 * 投稿本文から一覧表示用のテキストを作る
 *
 * テキストのみで規定文字数以内ならそのまま、画像を含む・規定文字数を超える場合は
 * 規定文字数で切り詰めて「...詳しく読む」リンクの対象（more = true）にする。
 *
 * @param string $content 投稿本文（post_content）
 * @return array { text, more }
 */
function wp_pagenation_get_body( $content ) {
	$has_image = (bool) preg_match( '/<img\b|<figure\b|<picture\b|\[gallery\b|<!--\s*wp:(image|gallery|cover|media-text)\b/i', $content );

	$text = wp_strip_all_tags( strip_shortcodes( $content ) );
	$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	$text = trim( preg_replace( '/[\s\x{00A0}]+/u', ' ', $text ) );

	$is_long = mb_strlen( $text, 'UTF-8' ) > WP_PAGENATION_BODY_LENGTH;
	if ( $is_long ) {
		$text = mb_substr( $text, 0, WP_PAGENATION_BODY_LENGTH, 'UTF-8' );
	}

	return array(
		'text' => $text,
		'more' => $is_long || $has_image,
	);
}

/**
 * スタイルシート・スクリプトの登録（読み込みはショートコード使用ページのみ）
 */
function wp_pagenation_register_assets() {
	$dir = get_stylesheet_directory() . '/wp-pagenation';
	$uri = get_stylesheet_directory_uri() . '/wp-pagenation';

	wp_register_style( 'wp-pagenation', $uri . '/style.css', array(), @filemtime( $dir . '/style.css' ) );
	wp_register_script( 'wp-pagenation', $uri . '/script.js', array(), @filemtime( $dir . '/script.js' ), true );
	wp_localize_script(
		'wp-pagenation',
		'wpPagenation',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => WP_PAGENATION_ACTION,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wp_pagenation_register_assets' );

/**
 * ショートコード [pagenation]
 *
 * 1ページ目はサーバー側で出力し、2ページ目以降は script.js が非同期で取得する。
 */
function wp_pagenation_shortcode() {
	wp_enqueue_style( 'wp-pagenation' );
	wp_enqueue_script( 'wp-pagenation' );

	$data = wp_pagenation_get_posts( 1 );

	ob_start();
	?>
	<section class="pagenation" data-total-pages="<?php echo esc_attr( $data['totalPages'] ); ?>">
		<?php if ( empty( $data['items'] ) ) : ?>
			<p class="pagenation__message">投稿記事はありません。</p>
		<?php else : ?>
			<ul class="pagenation__list">
				<?php foreach ( $data['items'] as $item ) : ?>
					<li class="pagenation__item">
						<div class="pagenation__thumb">
							<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" loading="lazy">
						</div>
						<div class="pagenation__content">
							<time class="pagenation__date" datetime="<?php echo esc_attr( $item['datetime'] ); ?>"><?php echo esc_html( $item['date'] ); ?></time>
							<h3 class="pagenation__title"><?php echo esc_html( $item['title'] ); ?></h3>
							<p class="pagenation__body"><?php echo esc_html( $item['body'] ); ?><?php if ( $item['more'] ) : ?><a class="pagenation__more" href="<?php echo esc_url( $item['url'] ); ?>">...詳しく読む</a><?php endif; ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<nav class="pagenation__nav" aria-label="ページネーション"></nav>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'pagenation', 'wp_pagenation_shortcode' );

/**
 * 非同期取得用エンドポイント（admin-ajax.php?action=wp_pagenation_fetch&page=N）
 */
function wp_pagenation_ajax() {
	$page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
	wp_send_json_success( wp_pagenation_get_posts( $page ) );
}
add_action( 'wp_ajax_' . WP_PAGENATION_ACTION, 'wp_pagenation_ajax' );
add_action( 'wp_ajax_nopriv_' . WP_PAGENATION_ACTION, 'wp_pagenation_ajax' );
