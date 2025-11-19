import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

const Edit = () => {
	return (
		<div { ...useBlockProps() }>
			KWK Recipe Grid block is alive 🧁 (editor preview)
		</div>
	);
};

// Dynamic block: front-end markup comes from PHP, so we save nothing.
const Save = () => null;

registerBlockType( 'kwk/recipe-grid', {
	edit: Edit,
	save: Save,
} );
