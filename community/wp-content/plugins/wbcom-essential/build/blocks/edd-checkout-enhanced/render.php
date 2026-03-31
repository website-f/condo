<?php
/**
 * Server-side render for EDD Enhanced Checkout block.
 *
 * @package WBCOM_Essential
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail early if EDD is not active.
if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
	echo '<div class="wbcom-edd-checkout__notice">';
	echo '<p>' . esc_html__( 'Easy Digital Downloads is required for this block.', 'wbcom-essential' ) . '</p>';
	echo '</div>';
	return;
}

// Extract attributes.
$show_progress_bar     = $attributes['showProgressBar'] ?? true;
$show_trust_badges     = $attributes['showTrustBadges'] ?? true;
$trust_badge_text      = $attributes['trustBadgeText'] ?? __( 'Secure checkout powered by Stripe', 'wbcom-essential' );
$show_reviews          = $attributes['showReviews'] ?? true;
$review_count          = $attributes['reviewCount'] ?? 3;
$show_recommendations  = $attributes['showRecommendations'] ?? true;
$recommendation_count  = $attributes['recommendationCount'] ?? 3;

// Unique ID for scoping.
$block_id = wp_unique_id( 'wbcom-edd-checkout-' );

// Wrapper attributes with BEM root class.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'wbcom-edd-checkout',
		'id'    => esc_attr( $block_id ),
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes() ?>>

	<?php if ( $show_progress_bar ) : ?>
		<div class="wbcom-edd-checkout__progress" role="navigation" aria-label="<?php esc_attr_e( 'Checkout steps', 'wbcom-essential' ); ?>">
			<ol class="wbcom-edd-checkout__steps">

				<li class="wbcom-edd-checkout__step wbcom-edd-checkout__step--completed" aria-label="<?php esc_attr_e( 'Cart - completed', 'wbcom-essential' ); ?>">
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--start"></div>
					<div class="wbcom-edd-checkout__step-circle" aria-hidden="true">
						<svg class="wbcom-edd-checkout__step-check" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="16" height="16">
							<path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--end wbcom-edd-checkout__step-connector--filled"></div>
					<span class="wbcom-edd-checkout__step-label"><?php esc_html_e( 'Cart', 'wbcom-essential' ); ?></span>
				</li>

				<li class="wbcom-edd-checkout__step wbcom-edd-checkout__step--active" aria-current="step" aria-label="<?php esc_attr_e( 'Details - current step', 'wbcom-essential' ); ?>">
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--start wbcom-edd-checkout__step-connector--filled"></div>
					<div class="wbcom-edd-checkout__step-circle" aria-hidden="true">
						<span class="wbcom-edd-checkout__step-number">2</span>
					</div>
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--end"></div>
					<span class="wbcom-edd-checkout__step-label"><?php esc_html_e( 'Details', 'wbcom-essential' ); ?></span>
				</li>

				<li class="wbcom-edd-checkout__step wbcom-edd-checkout__step--upcoming" aria-label="<?php esc_attr_e( 'Payment - upcoming', 'wbcom-essential' ); ?>">
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--start"></div>
					<div class="wbcom-edd-checkout__step-circle" aria-hidden="true">
						<span class="wbcom-edd-checkout__step-number">3</span>
					</div>
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--end"></div>
					<span class="wbcom-edd-checkout__step-label"><?php esc_html_e( 'Payment', 'wbcom-essential' ); ?></span>
				</li>

				<li class="wbcom-edd-checkout__step wbcom-edd-checkout__step--upcoming" aria-label="<?php esc_attr_e( 'Complete - upcoming', 'wbcom-essential' ); ?>">
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--start"></div>
					<div class="wbcom-edd-checkout__step-circle" aria-hidden="true">
						<span class="wbcom-edd-checkout__step-number">4</span>
					</div>
					<div class="wbcom-edd-checkout__step-connector wbcom-edd-checkout__step-connector--end wbcom-edd-checkout__step-connector--last"></div>
					<span class="wbcom-edd-checkout__step-label"><?php esc_html_e( 'Complete', 'wbcom-essential' ); ?></span>
				</li>

			</ol>
		</div>
	<?php endif; ?>

	<div class="wbcom-edd-checkout__form-wrap">
		<?php echo do_shortcode( '[download_checkout]' ); ?>
	</div>

	<?php if ( $show_trust_badges ) : ?>
		<div class="wbcom-edd-checkout__trust-section" aria-label="<?php esc_attr_e( 'Security and trust information', 'wbcom-essential' ); ?>">

			<div class="wbcom-edd-checkout__trust-badges">
				<div class="wbcom-edd-checkout__trust-badge">
					<div class="wbcom-edd-checkout__trust-badge-icon wbcom-edd-checkout__trust-badge-icon--shield">
						<svg viewBox="0 0 24 24" fill="none" width="22" height="22" aria-hidden="true">
							<path d="M12 1L3 5v6c0 5.25 3.75 10.15 9 11.25C17.25 21.15 21 16.25 21 11V5l-9-4z" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
							<path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="wbcom-edd-checkout__trust-badge-content">
						<span class="wbcom-edd-checkout__trust-badge-title"><?php echo esc_html( $trust_badge_text ); ?></span>
						<span class="wbcom-edd-checkout__trust-badge-desc"><?php esc_html_e( 'Your payment information is encrypted and secure.', 'wbcom-essential' ); ?></span>
					</div>
				</div>

				<div class="wbcom-edd-checkout__trust-badge">
					<div class="wbcom-edd-checkout__trust-badge-icon wbcom-edd-checkout__trust-badge-icon--guarantee">
						<svg viewBox="0 0 24 24" fill="none" width="22" height="22" aria-hidden="true">
							<circle cx="12" cy="12" r="10" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.5"/>
							<path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="wbcom-edd-checkout__trust-badge-content">
						<span class="wbcom-edd-checkout__trust-badge-title"><?php esc_html_e( '14-Day Money Back', 'wbcom-essential' ); ?></span>
						<span class="wbcom-edd-checkout__trust-badge-desc"><?php esc_html_e( 'Full refund if you are not satisfied.', 'wbcom-essential' ); ?></span>
					</div>
				</div>

				<div class="wbcom-edd-checkout__trust-badge">
					<div class="wbcom-edd-checkout__trust-badge-icon wbcom-edd-checkout__trust-badge-icon--support">
						<svg viewBox="0 0 24 24" fill="none" width="22" height="22" aria-hidden="true">
							<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<div class="wbcom-edd-checkout__trust-badge-content">
						<span class="wbcom-edd-checkout__trust-badge-title"><?php esc_html_e( 'Priority Support', 'wbcom-essential' ); ?></span>
						<span class="wbcom-edd-checkout__trust-badge-desc"><?php esc_html_e( 'Dedicated support for all customers.', 'wbcom-essential' ); ?></span>
					</div>
				</div>
			</div>

			<div class="wbcom-edd-checkout__payment-methods">
				<span class="wbcom-edd-checkout__payment-label"><?php esc_html_e( 'Accepted payments', 'wbcom-essential' ); ?></span>
				<div class="wbcom-edd-checkout__payment-icons">
					<span class="wbcom-edd-checkout__payment-icon" title="Visa" aria-label="Visa">
						<svg viewBox="0 0 38 24" width="38" height="24" aria-hidden="true"><rect width="38" height="24" rx="3" fill="#1A1F71"/><path d="M15.7 16.4l1.6-9.8h2.5l-1.6 9.8h-2.5zm10.5-9.6c-.5-.2-1.3-.4-2.2-.4-2.5 0-4.2 1.3-4.2 3.1 0 1.4 1.2 2.1 2.2 2.6 1 .5 1.3.8 1.3 1.2 0 .6-.8 1-1.5 1-.9 0-1.5-.2-2.3-.5l-.3-.2-.3 2c.6.3 1.6.5 2.7.5 2.6 0 4.3-1.3 4.3-3.2 0-1.1-.6-1.9-2-2.6-.8-.4-1.3-.7-1.3-1.2 0-.4.4-.8 1.3-.8.8 0 1.3.2 1.7.3l.2.1.3-1.9zM30.8 6.6h-2c-.6 0-1 .2-1.3.7l-3.6 8.7h2.5l.5-1.4h3.1l.3 1.4h2.2l-1.7-9.4zm-3.2 6.1l1.3-3.5.7 3.5h-2zM14.1 6.6l-2.3 6.7-.3-1.2c-.4-1.5-1.8-3.1-3.4-3.9l2.2 8.2h2.6l3.8-9.8h-2.6z" fill="#fff"/><path d="M9.5 6.6H5.7l0 .2c3.1.8 5.1 2.7 6 5l-.9-4.4c-.1-.6-.6-.8-1.3-.8z" fill="#F9A533"/></svg>
					</span>
					<span class="wbcom-edd-checkout__payment-icon" title="Mastercard" aria-label="Mastercard">
						<svg viewBox="0 0 38 24" width="38" height="24" aria-hidden="true"><rect width="38" height="24" rx="3" fill="#252525"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="23" cy="12" r="7" fill="#F79E1B"/><path d="M19 7.3a7 7 0 010 9.4 7 7 0 000-9.4z" fill="#FF5F00"/></svg>
					</span>
					<span class="wbcom-edd-checkout__payment-icon" title="PayPal" aria-label="PayPal">
						<svg viewBox="0 0 38 24" width="38" height="24" aria-hidden="true"><rect width="38" height="24" rx="3" fill="#fff" stroke="#e6e6e6"/><path d="M27.1 8.4c.1-.6.1-1-.1-1.4-.3-.5-.9-.7-1.7-.7h-3.8c-.2 0-.4.2-.5.4l-1.5 9.5c0 .2.1.3.3.3h2l.5-3.1v.1c0-.2.2-.4.5-.4h1c2 0 3.5-.8 4-3.1 0-.1 0-.2.1-.2.1.1.2-.5-.8-1.4z" fill="#003087"/><path d="M27.1 8.4c-.5 2.3-2 3.1-4 3.1h-1c-.2 0-.4.2-.5.4l-.6 3.8c0 .1.1.3.3.3h1.8c.2 0 .4-.1.4-.3v-.1l.3-2.2v-.1c0-.2.2-.3.4-.3h.3c1.7 0 3.1-.7 3.5-2.8.2-.9.1-1.6-.2-2.1l.3.3z" fill="#009cde"/><path d="M16.4 8.4c0-.2.2-.4.5-.4h3.8c.4 0 .9 0 1.2.1.1 0 .2.1.3.1.1 0 .2.1.3.1.1 0 .1 0 .2.1.3.1.5.3.6.5.2-1.1 0-1.9-.6-2.6-.7-.8-2-1.1-3.6-1.1h-4.9c-.3 0-.5.2-.6.4l-2 12.9c0 .2.1.4.3.4h3l.8-4.7.7-5.8z" fill="#012169"/></svg>
					</span>
					<span class="wbcom-edd-checkout__payment-icon" title="Stripe" aria-label="Stripe">
						<svg viewBox="0 0 38 24" width="38" height="24" aria-hidden="true"><rect width="38" height="24" rx="3" fill="#6772E5"/><path d="M18 10.8c0-1-.5-1.3-1.5-1.3-.7 0-1.5.3-2.1.6l-.3-1.4c.7-.3 1.6-.6 2.7-.6 2 0 2.8.9 2.8 2.7v5.1h-1.5l-.1-.7c-.6.5-1.3.9-2.1.9-1.3 0-2.2-.7-2.2-2 0-1.6 1.5-2.3 3.8-2.7v-.6zm0 1.5c-1.4.3-2.1.7-2.1 1.4 0 .5.4.8.9.8.6 0 1-.3 1.2-.5v-1.7zm5.3-3.3c.7-.8 1.4-1.2 2.3-1.2 1.5 0 2.4 1.1 2.4 3v4.1h-1.7v-3.9c0-1.1-.4-1.6-1.2-1.6-.6 0-1 .3-1.4.7v4.8h-1.7V5.6h1.7v4.7l-.4-.3z" fill="#fff"/></svg>
					</span>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php
	// Reviews social proof section.
	if ( $show_reviews && function_exists( 'edd_reviews' ) && edd_reviews() ) :
		$cart_contents = edd_get_cart_contents();
		$product_ids   = array();

		if ( ! empty( $cart_contents ) ) {
			$product_ids = wp_list_pluck( $cart_contents, 'id' );
		}

		// Gather reviews from cart products, or fallback to all recent reviews.
		$all_reviews = array();

		if ( ! empty( $product_ids ) ) {
			foreach ( $product_ids as $pid ) {
				$product_reviews = get_comments(
					array(
						'post_id' => $pid,
						'type'    => 'edd_review',
						'status'  => 'approve',
						'number'  => $review_count,
						'orderby' => 'comment_date_gmt',
						'order'   => 'DESC',
					)
				);
				if ( ! empty( $product_reviews ) ) {
					$all_reviews = array_merge( $all_reviews, $product_reviews );
				}
			}
		}

		// Fallback: get recent reviews from any product.
		if ( empty( $all_reviews ) ) {
			$all_reviews = get_comments(
				array(
					'type'    => 'edd_review',
					'status'  => 'approve',
					'number'  => $review_count,
					'orderby' => 'comment_date_gmt',
					'order'   => 'DESC',
				)
			);
		}

		// Deduplicate and limit.
		$seen_ids    = array();
		$unique      = array();
		foreach ( $all_reviews as $r ) {
			if ( ! isset( $seen_ids[ $r->comment_ID ] ) ) {
				$seen_ids[ $r->comment_ID ] = true;
				$unique[]                   = $r;
			}
			if ( count( $unique ) >= $review_count ) {
				break;
			}
		}
		$all_reviews = $unique;

		if ( ! empty( $all_reviews ) ) :
			// Calculate aggregate stats.
			$total_rating = 0;
			$rating_count = 0;
			foreach ( $all_reviews as $rev ) {
				$rating = (int) get_comment_meta( $rev->comment_ID, 'edd_rating', true );
				if ( $rating > 0 ) {
					$total_rating += $rating;
					$rating_count++;
				}
			}
			$avg_rating = $rating_count > 0 ? round( $total_rating / $rating_count, 1 ) : 0;
			?>
			<div class="wbcom-edd-checkout__reviews">
				<div class="wbcom-edd-checkout__reviews-header">
					<h3 class="wbcom-edd-checkout__reviews-title"><?php esc_html_e( 'What Our Customers Say', 'wbcom-essential' ); ?></h3>
					<?php if ( $avg_rating > 0 ) : ?>
						<div class="wbcom-edd-checkout__reviews-aggregate">
							<div class="wbcom-edd-checkout__reviews-stars" aria-label="<?php echo esc_attr( sprintf( '%s out of 5 stars', $avg_rating ) ); ?>">
								<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
									<?php if ( $i <= floor( $avg_rating ) ) : ?>
										<svg class="wbcom-edd-checkout__star wbcom-edd-checkout__star--filled" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><path d="M10 1l2.4 5.5L18 7.3l-4 4.2 1 5.9L10 14.8l-5 2.6 1-5.9-4-4.2 5.6-.8z" fill="currentColor"/></svg>
									<?php elseif ( $i - $avg_rating < 1 ) : ?>
										<svg class="wbcom-edd-checkout__star wbcom-edd-checkout__star--half" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><defs><linearGradient id="half-<?php echo esc_attr( $block_id ); ?>"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#e2e8f0"/></linearGradient></defs><path d="M10 1l2.4 5.5L18 7.3l-4 4.2 1 5.9L10 14.8l-5 2.6 1-5.9-4-4.2 5.6-.8z" fill="url(#half-<?php echo esc_attr( $block_id ); ?>)"/></svg>
									<?php else : ?>
										<svg class="wbcom-edd-checkout__star wbcom-edd-checkout__star--empty" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><path d="M10 1l2.4 5.5L18 7.3l-4 4.2 1 5.9L10 14.8l-5 2.6 1-5.9-4-4.2 5.6-.8z" fill="currentColor"/></svg>
									<?php endif; ?>
								<?php endfor; ?>
							</div>
							<span class="wbcom-edd-checkout__reviews-avg"><?php echo esc_html( $avg_rating ); ?>/5</span>
							<span class="wbcom-edd-checkout__reviews-count">
								<?php
								printf(
									/* translators: %d: Number of reviews. */
									esc_html( _n( 'from %d review', 'from %d reviews', $rating_count, 'wbcom-essential' ) ),
									$rating_count
								);
								?>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<div class="wbcom-edd-checkout__reviews-grid">
					<?php foreach ( $all_reviews as $review ) :
						$rating       = (int) get_comment_meta( $review->comment_ID, 'edd_rating', true );
						$review_title = get_comment_meta( $review->comment_ID, 'edd_review_title', true );
						$author       = $review->comment_author;
						$content      = wp_trim_words( $review->comment_content, 25, '...' );
						$product      = get_the_title( $review->comment_post_ID );
						?>
						<div class="wbcom-edd-checkout__review-card">
							<div class="wbcom-edd-checkout__review-stars" aria-label="<?php echo esc_attr( sprintf( '%d out of 5 stars', $rating ) ); ?>">
								<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
									<svg class="wbcom-edd-checkout__star <?php echo $i <= $rating ? 'wbcom-edd-checkout__star--filled' : 'wbcom-edd-checkout__star--empty'; ?>" viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><path d="M10 1l2.4 5.5L18 7.3l-4 4.2 1 5.9L10 14.8l-5 2.6 1-5.9-4-4.2 5.6-.8z" fill="currentColor"/></svg>
								<?php endfor; ?>
							</div>
							<?php if ( $review_title ) : ?>
								<p class="wbcom-edd-checkout__review-title"><?php echo esc_html( $review_title ); ?></p>
							<?php endif; ?>
							<p class="wbcom-edd-checkout__review-text"><?php echo esc_html( $content ); ?></p>
							<div class="wbcom-edd-checkout__review-meta">
								<span class="wbcom-edd-checkout__review-author"><?php echo esc_html( $author ); ?></span>
								<?php if ( $product ) : ?>
									<span class="wbcom-edd-checkout__review-product"><?php echo esc_html( $product ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	// Product recommendations section.
	if ( $show_recommendations && function_exists( 'edd_rp_get_multi_suggestions' ) ) :
		$cart_contents = edd_get_cart_contents();

		if ( ! empty( $cart_contents ) ) :
			$cart_ids       = wp_list_pluck( $cart_contents, 'id' );
			$user_id        = is_user_logged_in() ? get_current_user_id() : false;
			$recommendations = edd_rp_get_multi_suggestions( $cart_ids, $user_id, $recommendation_count );

			if ( ! empty( $recommendations ) ) :
				?>
				<div class="wbcom-edd-checkout__recommendations">
					<h3 class="wbcom-edd-checkout__recommendations-title"><?php esc_html_e( 'Customers Also Purchased', 'wbcom-essential' ); ?></h3>
					<div class="wbcom-edd-checkout__recommendations-grid">
						<?php foreach ( $recommendations as $download_id => $count ) :
							$download = edd_get_download( $download_id );
							if ( ! $download ) {
								continue;
							}
							$price     = edd_price( $download_id, false );
							$thumbnail = get_the_post_thumbnail( $download_id, 'thumbnail' );
							$permalink = get_permalink( $download_id );

							// Get review data if EDD Reviews is active.
							$rec_rating = 0;
							$rec_count  = 0;
							if ( function_exists( 'edd_reviews' ) && edd_reviews() ) {
								$rec_reviews = get_comments(
									array(
										'post_id' => $download_id,
										'type'    => 'edd_review',
										'status'  => 'approve',
										'count'   => true,
									)
								);
								$rec_count = (int) $rec_reviews;
								if ( $rec_count > 0 ) {
									$ratings_sum = 0;
									$review_objs = get_comments(
										array(
											'post_id' => $download_id,
											'type'    => 'edd_review',
											'status'  => 'approve',
											'number'  => 100,
										)
									);
									foreach ( $review_objs as $ro ) {
										$ratings_sum += (int) get_comment_meta( $ro->comment_ID, 'edd_rating', true );
									}
									$rec_rating = round( $ratings_sum / $rec_count, 1 );
								}
							}
							?>
							<div class="wbcom-edd-checkout__rec-card">
								<?php if ( $thumbnail ) : ?>
									<a href="<?php echo esc_url( $permalink ); ?>" class="wbcom-edd-checkout__rec-thumb">
										<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress core function ?>
									</a>
								<?php endif; ?>
								<div class="wbcom-edd-checkout__rec-info">
									<a href="<?php echo esc_url( $permalink ); ?>" class="wbcom-edd-checkout__rec-name"><?php echo esc_html( get_the_title( $download_id ) ); ?></a>
									<?php if ( $rec_rating > 0 ) : ?>
										<div class="wbcom-edd-checkout__rec-rating">
											<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
												<svg class="wbcom-edd-checkout__star <?php echo $i <= round( $rec_rating ) ? 'wbcom-edd-checkout__star--filled' : 'wbcom-edd-checkout__star--empty'; ?>" viewBox="0 0 20 20" width="12" height="12" aria-hidden="true"><path d="M10 1l2.4 5.5L18 7.3l-4 4.2 1 5.9L10 14.8l-5 2.6 1-5.9-4-4.2 5.6-.8z" fill="currentColor"/></svg>
											<?php endfor; ?>
											<span class="wbcom-edd-checkout__rec-review-count">(<?php echo esc_html( $rec_count ); ?>)</span>
										</div>
									<?php endif; ?>
									<div class="wbcom-edd-checkout__rec-price"><?php echo wp_kses_post( $price ); ?></div>
								</div>
								<div class="wbcom-edd-checkout__rec-action">
									<?php
									echo edd_get_purchase_link(
										array(
											'download_id' => $download_id,
											'text'        => __( 'Add to Cart', 'wbcom-essential' ),
											'style'       => 'button',
											'class'       => 'wbcom-edd-checkout__rec-btn',
										)
									);
									?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

</div>
