<template>
	<tr>
		<td class="signup-code-code">
			<template v-if="! isEditing">
				{{ code }}
			</template>

			<template v-if="isEditing">
				<input
					class="new-item-field"
					id="add-signup-code-input"
					v-bind:disabled="isLoading"
					v-model="code"
				>
			</template>
		</td>

		<td class="signup-code-member-type">
			<template v-if="! isEditing">
				{{ memberType.name }}
			</template>

			<template v-if="isEditing">
				<SignupCodeMemberTypeSelector v-model="memberTypeSlug" :wpPostId="wpPostId" />
			</template>
		</td>

		<td class="signup-code-group">
			{{ group.name }}
		</td>

		<td class="signup-code-actions">
			<a href="#" v-if="! isEditing" v-on:click="onEditClick">{{ strings.edit }}</a><a href="#" v-if="isEditing" v-on:click="onSaveClick"><strong>{{ strings.save }}</strong></a> | <a href="#" v-on:click="onDeleteClick">{{ strings.delete }}</a>
		</td>
	</tr>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import SignupCodeTools from '../mixins/SignupCodeTools.js'

	import SignupCodeMemberTypeSelector from './SignupCodeMemberTypeSelector.vue'
	import SignupCodeGroupSelector from './SignupCodeGroupSelector.vue'

	export default {
		components: {
			SignupCodeGroupSelector,
			SignupCodeMemberTypeSelector
		},

		computed: {
			isEditing: {
				get() {
					return this.$store.state.isEditing.hasOwnProperty( this.id )
				},
				set( value ) {
					this.$store.commit( 'setIsEditing', { key: this.id, value } )
				}
			},
		},
		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		},
		methods: {
			onDeleteClick( event ) {
				event.preventDefault()

				let item = this
				item.isLoading = true

				item.$store.dispatch( 'submitDeleteSignupCode', { wpPostId: item.wpPostId } )
					.then( item.checkStatus )
					.then( item.parseJSON )
					.then( function( data ) {
						item.isLoading = false
						item.$store.commit( 'removeSignupCode', { wpPostId: item.wpPostId } )
					}, function( data ) {
						item.isLoading = false
					} )
			},
			onEditClick( event ) {
				event.preventDefault()
				this.isEditing = true
			},
			onSaveClick( event ) {
				event.preventDefault()
				let item = this
				item.isLoading = true

				item.$store.dispatch( 'submitEmailDomain', { domain: item.domain, key: item.domainKey } )
					.then( item.checkStatus )
					.then( item.parseJSON )
					.then( function( data ) {
						item.isLoading = false
						item.isEditing = false
					}, function( data ) {
						item.isLoading = false
					} )
			}
		},

		mixins: [
			AjaxTools,
			SignupCodeTools
		],

		props: [ 'wpPostId' ]
	}
</script>
