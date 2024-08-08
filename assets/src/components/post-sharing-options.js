/* global openlabBlocksPostVisibility */

import { VisuallyHidden } from '@wordpress/components'
import { PluginDocumentSettingPanel } from '@wordpress/edit-post'
import { registerPlugin } from '@wordpress/plugins'
import { useDispatch, useSelect } from '@wordpress/data'
import { __ } from '@wordpress/i18n'

const PostSharingOptions = ({}) => {
	const { blogPublic } = openlabBlocksPostVisibility

	const { editPost } = useDispatch( 'core/editor' )

	const blogPublicInt = parseInt( blogPublic )

	const { postVisibility } = useSelect( ( select ) => {
		const postMeta = select( 'core/editor' ).getEditedPostAttribute( 'meta' )

		const defaultVisibility = blogPublicInt >= 0 ? 'default' : 'members-only'

		return {
			postVisibility: postMeta.openlab_post_visibility || defaultVisibility,
		}
	} )

	if ( blogPublicInt < -1 ) {
		return null
	}

	const onChange = ( value ) => {
		editPost( { meta: { 'openlab_post_visibility': value } } )
	}

	const visibilityOptions = [
		{
			value: 'group-members-only',
			label: __( 'Site Members', 'commons-in-a-box' ),
			info: __( 'Only members of the current site can see this post. This will override the Public visibility setting above.', 'commons-in-a-box' )
		},
		{
			value: 'members-only',
			label: __( 'Community members only', 'commons-in-a-box' ),
			info: __( 'Only logged-in members of the community can see this post.', 'commons-in-a-box' )
		}
	]

	if ( blogPublicInt >= 0 ) {
		visibilityOptions.push( {
			value: 'default',
			label: __( 'Everyone', 'commons-in-a-box' ),
			info: __( 'Everyone who can view this site can see this post.', 'commons-in-a-box' )
		} )
	}

	return (
		<PluginDocumentSettingPanel
			name="post-sharing-options"
			title={ __( 'More visibility options', 'commons-in-a-box' ) }
			className="post-sharing-options"
		>
			<fieldset className="editor-post-visibility__fieldset">
				<VisuallyHidden as="legend">
					{ __( 'Sharing', 'commons-in-a-box' ) }
				</VisuallyHidden>

				<p>{ __( 'Control who can see this post.', 'commons-in-a-box' ) }</p>

				{ visibilityOptions.map( ( option ) => (
					<PostSharingChoice
						key={ option.value }
						instanceId="post-sharing-options"
						value={ option.value }
						label={ option.label }
						info={ option.info }
						onChange={ ( event ) => onChange( event.target.value ) }
						checked={ postVisibility === option.value }
					/>
				) ) }
			</fieldset>
		</PluginDocumentSettingPanel>
	)
}

function PostSharingChoice( { instanceId, value, label, info, ...props } ) {
	return (
		<div className="editor-post-visibility__choice">
			<input
				type="radio"
				name={ `editor-post-visibility__setting-${ instanceId }` }
				value={ value }
				id={ `editor-post-${ value }-${ instanceId }` }
				aria-describedby={ `editor-post-${ value }-${ instanceId }-description` }
				className="editor-post-visibility__radio"
				{ ...props }
			/>
			<label
				htmlFor={ `editor-post-${ value }-${ instanceId }` }
				className="editor-post-visibility__label"
			>
				{ label }
			</label>
			<p
				id={ `editor-post-${ value }-${ instanceId }-description` }
				className="editor-post-visibility__info"
			>
				{ info }
			</p>
		</div>
	);
}

registerPlugin(
	'post-sharing-options',
	{ render: PostSharingOptions }
)
