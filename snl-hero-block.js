( function( blocks, element, editor, components ) {
    var el = element.createElement;
    var InspectorControls = editor.InspectorControls;
    var MediaUpload = editor.MediaUpload;
    var ColorPalette = components.ColorPalette;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var Button = components.Button;

    blocks.registerBlockType( 'snl/hero-signup-block', {
        title: 'Newsletter Hero Section',
        icon: 'email',
        category: 'common',
        attributes: {
            headline: { type: 'string', default: 'Join the Journey—Get Updates Direct to Your Inbox' },
            subheadline: { type: 'string', default: 'Subscribe to my newsletter and be the first to know about new projects, behind-the-scenes insights, and exclusive content.' },
            buttonText: { type: 'string', default: 'Subscribe Now' },
            backgroundImage: { type: 'string', default: SNL_HERO_BACKGROUND },
            textColor: { type: 'string', default: '#ffffff' },
            buttonColor: { type: 'string', default: '#ff6600' },
        },
        edit: function( props ) {
            var attributes = props.attributes;

            return [
                el( InspectorControls, {},
                    el( PanelBody, { title: 'Settings' },
                        el( TextControl, {
                            label: 'Headline',
                            value: attributes.headline,
                            onChange: function( value ) {
                                props.setAttributes( { headline: value } );
                            },
                        }),
                        el( TextControl, {
                            label: 'Subheadline',
                            value: attributes.subheadline,
                            onChange: function( value ) {
                                props.setAttributes( { subheadline: value } );
                            },
                        }),
                        el( TextControl, {
                            label: 'Button Text',
                            value: attributes.buttonText,
                            onChange: function( value ) {
                                props.setAttributes( { buttonText: value } );
                            },
                        }),
                        el( MediaUpload, {
                            onSelect: function( media ) {
                                props.setAttributes( { backgroundImage: media.url } );
                            },
                            allowedTypes: 'image',
                            render: function( obj ) {
                                return el( Button, {
                                    onClick: obj.open
                                }, 'Select Background Image' );
                            }
                        }),
                        el( 'div', {},
                            el( 'label', {}, 'Text Color' ),
                            el( ColorPalette, {
                                value: attributes.textColor,
                                onChange: function( color ) {
                                    props.setAttributes( { textColor: color } );
                                }
                            })
                        ),
                        el( 'div', {},
                            el( 'label', {}, 'Button Color' ),
                            el( ColorPalette, {
                                value: attributes.buttonColor,
                                onChange: function( color ) {
                                    props.setAttributes( { buttonColor: color } );
                                }
                            })
                        )
                    )
                ),
                el(
                    'div',
                    { className: 'snl-hero-block-editor', style: { backgroundImage: 'url(' + attributes.backgroundImage + ')', color: attributes.textColor } },
                    el( 'h1', {}, attributes.headline ),
                    el( 'p', {}, attributes.subheadline ),
                    el( 'button', { style: { backgroundColor: attributes.buttonColor } }, attributes.buttonText )
                )
            ];
        },
        save: function( props ) {
            return '[snl-hero-signup]';
        }
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.editor,
    window.wp.components
);
