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
	$variant = isset($attributes['variant']) ? $attributes['variant'] : 'archive';

	ob_start();
?>
	<div class="kwk-grid kwk-grid--<?php echo esc_attr($variant); ?>">
		<article class="kwk-card kwk-card--recipe">
			<h3 class="kwk-card__title">
				KWK Recipe Grid dynamic block is working 🎉
			</h3>
			<p class="kwk-card__excerpt">
				This HTML is coming from the PHP <code>kwk_render_recipe_grid_block()</code> function.
			</p>
		</article>
	</div>
<?php

	return ob_get_clean();
}
