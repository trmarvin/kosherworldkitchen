<?php

/**
 * Plugin Name:       KWK Recipe Grid
 * Description:       Custom recipe grid block for Kosher World Kitchen.
 * Version:           0.1.0
 * Author:            Tamar / KWK
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Registers the block and attaches the render callback.
 */
function create_block_kwk_recipe_grid_block_init()
{
	register_block_type(
		__DIR__ . '/build/kwk-recipe-grid',
		[
			'render_callback' => 'kwk_render_recipe_grid_block',
		]
	);
}
add_action('init', 'create_block_kwk_recipe_grid_block_init');

/**
 * Server-side render callback for the KWK Recipe Grid block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Saved block content (ignored here).
 * @param object $block      Block instance.
 *
 * @return string
 */
function kwk_render_recipe_grid_block($attributes, $content, $block)
{

	// 1. Basic attributes.
	$variant = isset($attributes['variant']) ? $attributes['variant'] : 'archive';

	$columns = isset($attributes['columns']) ? (int) $attributes['columns'] : 3;
	$columns = max(1, min(4, $columns)); // clamp 1–4.

	// 2. Advanced query attributes.
	$use_global = ! empty($attributes['useGlobalQuery']);

	$posts_to_show = isset($attributes['postsToShow']) ? (int) $attributes['postsToShow'] : 6;
	if ($posts_to_show < 1) {
		$posts_to_show = 1;
	}
	if ($posts_to_show > 50) {
		$posts_to_show = 50; // safety cap.
	}

	$order = isset($attributes['order']) ? strtolower($attributes['order']) : 'desc';
	$order = ('asc' === $order) ? 'ASC' : 'DESC';

	$order_by        = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date';
	$allowed_orderby = ['date', 'title', 'rand'];
	if (! in_array($order_by, $allowed_orderby, true)) {
		$order_by = 'date';
	}

	// Developer override: categories attribute (comma-separated slugs).
	$categories_raw = isset($attributes['categories']) ? trim((string) $attributes['categories']) : '';

	// 2b. Filter UI flags.
	$show_category_filter   = ! empty($attributes['showCategoryFilter']);
	$show_tag_filter        = ! empty($attributes['showTagFilter']);
	$show_ingredient_filter = ! empty($attributes['showIngredientFilter']);

	// Selected filters from query string (GET).
	$selected_category   = isset($_GET['kwk_cat']) ? sanitize_text_field(wp_unslash($_GET['kwk_cat'])) : '';
	$selected_tag        = isset($_GET['kwk_tag']) ? sanitize_text_field(wp_unslash($_GET['kwk_tag'])) : '';
	$selected_ingredient = isset($_GET['kwk_ing']) ? sanitize_text_field(wp_unslash($_GET['kwk_ing'])) : '';
	$selected_keyword    = isset($_GET['kwk_q']) ? sanitize_text_field(wp_unslash($_GET['kwk_q'])) : '';

	// 3. Decide which query to use: global or custom.
	if ($use_global && (is_archive() || is_search())) {

		// Use the main query (archive/search context).
		global $wp_query;
		$query              = $wp_query;
		$using_global_query = true;
	} else {

		// Build a custom query.
		$using_global_query = false;

		$query_args = [
			'post_type'      => 'post',
			'posts_per_page' => $posts_to_show,
			'post_status'    => 'publish',
			'order'          => $order,
			'orderby'        => $order_by,
		];

		// Pagination (for when this block lives on archives/search).
		$paged = max(1, (int) get_query_var('paged'));
		if ($paged > 1) {
			$query_args['paged'] = $paged;
		}

		// Keyword search:
		// - If we're on a native search results page and variant=search, use that.
		// - Otherwise, use kwk_q from the filter form if provided.
		$search_term = '';
		if ('search' === $variant && is_search()) {
			$search_term = get_search_query();
		} elseif ('' !== $selected_keyword) {
			$search_term = $selected_keyword;
		}
		if ('' !== $search_term) {
			$query_args['s'] = $search_term;
		}

		// Build tax_query from selected filters.
		$tax_query = [];

		if ('' !== $selected_category) {
			$tax_query[] = [
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $selected_category,
			];
		}

		if ('' !== $selected_tag) {
			$tax_query[] = [
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $selected_tag,
			];
		}

		if ('' !== $selected_ingredient) {
			$tax_query[] = [
				'taxonomy' => 'wprm_ingredient', // adjust if your WPRM ingredient taxonomy slug differs.
				'field'    => 'slug',
				'terms'    => $selected_ingredient,
			];
		}

		// Developer override via "categories" attribute (only if no category filter selected).
		if ('' !== $categories_raw && '' === $selected_category) {
			$tax_query[] = [
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => array_map('trim', explode(',', $categories_raw)),
			];
		}

		if (! empty($tax_query)) {
			if (count($tax_query) > 1) {
				$tax_query['relation'] = 'AND';
			}
			$query_args['tax_query'] = $tax_query;
		}

		$query = new WP_Query($query_args);
	}

	// 4. Bail early if no posts.
	if (! $query->have_posts()) {
		return '<p>No recipes found.</p>';
	}

	// 5. Build wrapper classes.
	$wrapper_classes = implode(
		' ',
		[
			'kwk-grid',
			'kwk-grid--' . $variant,
			'kwk-grid--cols-' . $columns,
		]
	);

	ob_start();
?>

	<?php
	// 6. Render filter form (only when not using global query).
	if (! $use_global && ($show_category_filter || $show_tag_filter || $show_ingredient_filter)) :
	?>
		<form class="kwk-grid-filters" method="get">
			<div class="kwk-grid-filters__inner">

				<?php if ($show_category_filter) : ?>
					<div class="kwk-grid-filters__field">
						<label for="kwk-filter-category">Category</label>
						<select id="kwk-filter-category" name="kwk_cat">
							<option value="">All</option>
							<?php
							$categories = get_terms(
								[
									'taxonomy'   => 'category',
									'hide_empty' => true,
								]
							);
							if (! is_wp_error($categories)) :
								foreach ($categories as $cat) :
							?>
									<option
										value="<?php echo esc_attr($cat->slug); ?>"
										<?php selected($selected_category, $cat->slug); ?>>
										<?php echo esc_html($cat->name); ?>
									</option>
							<?php
								endforeach;
							endif;
							?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ($show_tag_filter) : ?>
					<div class="kwk-grid-filters__field">
						<label for="kwk-filter-tag">Tag</label>
						<select id="kwk-filter-tag" name="kwk_tag">
							<option value="">All</option>
							<?php
							$tags = get_terms(
								[
									'taxonomy'   => 'post_tag',
									'hide_empty' => true,
								]
							);
							if (! is_wp_error($tags)) :
								foreach ($tags as $tag) :
							?>
									<option
										value="<?php echo esc_attr($tag->slug); ?>"
										<?php selected($selected_tag, $tag->slug); ?>>
										<?php echo esc_html($tag->name); ?>
									</option>
							<?php
								endforeach;
							endif;
							?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ($show_ingredient_filter) : ?>
					<div class="kwk-grid-filters__field">
						<label for="kwk-filter-ingredient">Ingredient</label>
						<select id="kwk-filter-ingredient" name="kwk_ing">
							<option value="">All</option>
							<?php
							$ingredients = get_terms(
								[
									'taxonomy'   => 'wprm_ingredient',
									'hide_empty' => true,
								]
							);
							if (! is_wp_error($ingredients)) :
								foreach ($ingredients as $ing) :
							?>
									<option
										value="<?php echo esc_attr($ing->slug); ?>"
										<?php selected($selected_ingredient, $ing->slug); ?>>
										<?php echo esc_html($ing->name); ?>
									</option>
							<?php
								endforeach;
							endif;
							?>
						</select>
					</div>
				<?php endif; ?>

				<div class="kwk-grid-filters__field">
					<label for="kwk-filter-q">Keyword</label>
					<input
						type="text"
						id="kwk-filter-q"
						name="kwk_q"
						value="<?php echo esc_attr($selected_keyword); ?>" />
				</div>

				<div class="kwk-grid-filters__actions">
					<button type="submit">Filter recipes</button>
				</div>
			</div>
		</form>
	<?php endif; ?>

	<div class="<?php echo esc_attr($wrapper_classes); ?>">

		<?php while ($query->have_posts()) : $query->the_post(); ?>

			<?php
			// Base card classes + variant modifier.
			$card_classes = implode(
				' ',
				[
					'kwk-card',
					'kwk-card--recipe',
					'kwk-card--' . $variant,
				]
			);
			?>

			<article class="<?php echo esc_attr($card_classes); ?>">

				<?php if ('search' !== $variant) : ?>
					<a href="<?php the_permalink(); ?>" class="kwk-card__image-link">
						<?php if (has_post_thumbnail()) : ?>
							<?php the_post_thumbnail('medium_large', ['class' => 'kwk-card__image']); ?>
						<?php endif; ?>
					</a>
				<?php endif; ?>

				<div class="kwk-card__body">
					<h3 class="kwk-card__title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h3>

					<?php if ('inline' !== $variant) : ?>
						<p class="kwk-card__excerpt">
							<?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
						</p>
					<?php endif; ?>
				</div>

			</article>

		<?php endwhile; ?>

		<?php
		// Only reset postdata if we created our own query.
		if (! $using_global_query) {
			wp_reset_postdata();
		}
		?>

	</div>

<?php
	return ob_get_clean();
}
