/* global openlabBlocksPostVisibility */

import { VisuallyHidden } from '@wordpress/components'
import { PluginDocumentSettingPanel } from '@wordpress/edit-post'
import { registerPlugin } from '@wordpress/plugins'
import { useDispatch, useSelect } from '@wordpress/data'
import { __, sprintf } from '@wordpress/i18n'

const PostSharingOptions = ({}) => {
	const { siteIsPublic } = openlabBlocksPostVisibility

	const { editPost } = useDispatch( 'core/editor' )

	const { postVisibility } = useSelect( ( select ) => {
		const postMeta = select( 'core/editor' ).getEditedPostAttribute( 'meta' )

		return {
			// eslint-disable-next-line dot-notation
			postVisibility: postMeta['openlab_post_visibility'] || 'default'
		}
	} )

	if ( ! siteIsPublic ) {
		return null
	}

	const onChange = ( value ) => {
		editPost( { meta: { 'openlab_post_visibility': value } } )
	}

	const publicOverrideString = __( 'This will override the Public visibility setting above.', 'commons-in-a-box' )

	const groupMembersOnlyInfo = sprintf(
		/* translators: %s: string of text that explains that this setting will override the public visibility setting */
		__( 'Only site members can see this post. %s', 'commons-in-a-box' ),
		publicOverrideString
	)

	const loggedinOnlyInfo = sprintf(
		/* translators: %s: string of text that explains that this setting will override the public visibility setting */
		__( 'Only logged-in users can see this post. %s', 'commons-in-a-box' ),
		publicOverrideString
	)

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

				<PostSharingChoice
					instanceId="post-sharing-options"
					value="group-members-only"
					label={ __( 'Site Members', 'commons-in-a-box' ) }
					info={ groupMembersOnlyInfo }
					onChange={ ( event ) => onChange( event.target.value ) }
					checked={ postVisibility === 'group-members-only' }
				/>

				<PostSharingChoice
					instanceId="post-sharing-options"
					value="members-only"
					label={ __( 'Logged-in users only', 'commons-in-a-box' ) }
					info={ loggedinOnlyInfo }
					onChange={ ( event ) => onChange( event.target.value ) }
					checked={	postVisibility === 'members-only' }
				/>

				<PostSharingChoice
					instanceId="post-sharing-options"
					value="default"
					label={ __( 'Everyone', 'commons-in-a-box' ) }
					info={ __( 'Everyone who can view this site can see this post.', 'commons-in-a-box' ) }
					onChange={ ( event ) => onChange( event.target.value ) }
					checked={ postVisibility === 'default' }
				/>
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
