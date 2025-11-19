const VARIANT_OPTIONS = [
	{ label: 'Archive', value: 'archive' },
	{ label: 'Inline', value: 'inline' },
	{ label: 'Search', value: 'search' },
];

const Edit = ( { attributes, setAttributes } ) => {
	const { variant, columns } = attributes;

	const blockProps = useBlockProps( {
		className: `kwk-grid kwk-grid--${ variant } kwk-grid--cols-${ columns } is-editor-preview`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title="KWK Recipe Grid Settings" initialOpen={ true }>
					<SelectControl
						label="Variant"
						value={ variant }
						options={ VARIANT_OPTIONS }
						onChange={ ( value ) =>
							setAttributes( { variant: value } )
						}
					/>

					<RangeControl
						label="Columns"
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 1 }
						max={ 4 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="kwk-card kwk-card--recipe">
					<h3 className="kwk-card__title">
						KWK Recipe Grid ({ variant }, { columns} column
						{ columns === 1 ? '' : 's' }) – editor preview
					</h3>
					<p className="kwk-card__excerpt">
						This is just a preview. On the front end, the PHP
						render callback will output the real grid.
					</p>
				</div>
			</div>
		</>
	);
};

const Save = () => null;

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
} );

