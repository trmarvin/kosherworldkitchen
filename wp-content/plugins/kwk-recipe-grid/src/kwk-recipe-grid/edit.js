import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			KWK Recipe Grid block is alive 🧁 (editor preview)
		</div>
	);
}
