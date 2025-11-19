<?php
// This file is generated. Do not modify it manually.
return array(
	'kwk-recipe-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'kwk/recipe-grid',
		'title' => 'KWK Recipe Grid',
		'category' => 'widgets',
		'icon' => 'grid-view',
		'description' => 'Custom recipe grid for Kosher World Kitchen.',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'variant' => array(
				'type' => 'string',
				'default' => 'archive'
			),
			'columns' => array(
				'type' => 'number',
				'default' => 3
			),
			'postsToShow' => array(
				'type' => 'number',
				'default' => 6
			),
			'order' => array(
				'type' => 'string',
				'default' => 'desc'
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date'
			),
			'useGlobalQuery' => array(
				'type' => 'boolean',
				'default' => false
			),
			'categories' => array(
				'type' => 'string',
				'default' => ''
			),
			'showCategoryFilter' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showTagFilter' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showIngredientFilter' => array(
				'type' => 'boolean',
				'default' => false
			)
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	)
);
