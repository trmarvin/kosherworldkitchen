import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	TextControl
} from '@wordpress/components';
import metadata from './block.json';

const VARIANT_OPTIONS = [
	{ label: 'Archive', value: 'archive' },
	{ label: 'Inline', value: 'inline' },
	{ label: 'Search', value: 'search' },
];

const Edit = ({ attributes, setAttributes }) => {
	const {
		variant = 'archive',
		columns = 3,
		postsToShow = 6,
		order = 'desc',
		orderBy = 'date',
		useGlobalQuery = false,
		categories = '',
		showCategoryFilter = true,
		showTagFilter = false,
		showIngredientFilter = false,
	} = attributes;

	const blockProps = useBlockProps({
		className: `kwk-grid kwk-grid--${variant} kwk-grid--cols-${columns} is-editor-preview`,
	});

	return (
		<>
			<InspectorControls>
				{/* MAIN SETTINGS */}
				<PanelBody title="KWK Recipe Grid Settings" initialOpen={true}>
					<SelectControl
						label="Variant"
						value={variant}
						options={VARIANT_OPTIONS}
						onChange={(value) => setAttributes({ variant: value })}
					/>

					<RangeControl
						label="Columns"
						value={columns}
						onChange={(value) => setAttributes({ columns: value })}
						min={1}
						max={4}
					/>

					<ToggleControl
						label="Inherit global query"
						checked={useGlobalQuery}
						onChange={(value) => setAttributes({ useGlobalQuery: value })}
					/>

					<RangeControl
						label="Number of posts"
						value={postsToShow}
						onChange={(value) => setAttributes({ postsToShow: value })}
						min={1}
						max={24}
						disabled={useGlobalQuery}
					/>

					<SelectControl
						label="Order"
						value={order}
						options={[
							{ label: 'Descending (newest first)', value: 'desc' },
							{ label: 'Ascending (oldest first)', value: 'asc' },
						]}
						onChange={(value) => setAttributes({ order: value })}
						disabled={useGlobalQuery}
					/>

					<SelectControl
						label="Order by"
						value={orderBy}
						options={[
							{ label: 'Date', value: 'date' },
							{ label: 'Title', value: 'title' },
							{ label: 'Random', value: 'rand' },
						]}
						onChange={(value) => setAttributes({ orderBy: value })}
						disabled={useGlobalQuery}
					/>

					<TextControl
						label="Filter by categories (developer override)"
						value={categories}
						onChange={(value) => setAttributes({ categories: value })}
					/>
				</PanelBody>

				{/* FILTER UI PANEL */}
				<PanelBody title="Filter UI" initialOpen={false}>
					<ToggleControl
						label="Show category filter"
						checked={showCategoryFilter}
						onChange={(value) => setAttributes({ showCategoryFilter: value })}
					/>

					<ToggleControl
						label="Show tag filter"
						checked={showTagFilter}
						onChange={(value) => setAttributes({ showTagFilter: value })}
					/>

					<ToggleControl
						label="Show ingredient filter (WPRM)"
						checked={showIngredientFilter}
						onChange={(value) =>
							setAttributes({ showIngredientFilter: value })
						}
					/>
				</PanelBody>
			</InspectorControls>

			{/* EDITOR PREVIEW */}
			<div {...blockProps}>
				<div className="kwk-card kwk-card--recipe">
					<h3 className="kwk-card__title">
						KWK Recipe Grid ({variant}, {postsToShow} post
						{postsToShow === 1 ? '' : 's'}, {columns} column
						{columns === 1 ? '' : 's'}) – editor preview
					</h3>
					<p className="kwk-card__excerpt">
						This is a preview. The front end will output the real query
						via PHP.
					</p>
				</div>
			</div>
		</>
	);
};

// Dynamic block: front-end markup comes from PHP.
const Save = () => null;

registerBlockType(metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
});
