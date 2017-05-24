<template>
	<tr>
		<td class="signup-code-code">
			{{ code }}
		</td>
		<td class="signup-code-member-type">
			{{ memberType.name }}
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
	export default {
		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		},
		computed: {
			code: {
				get() {
					return this.$store.state.signupCodes[ this.wpPostId ].code
				},
				set( value ) {
					this.$store.commit( 'setSignupCodeProperty', {
						wpPostId: this.wpPostId,
						key: 'code',
						value: value
					} )
				}
			},
			group: {
				get() {
					return this.$store.state.signupCodes[ this.wpPostId ].group
				}
			},
			id: {
				get() {
					return 'signupCode-' + this.wpPostId
				}
			},
			isEditing: {
				get() {
					return this.$store.state.isEditing.hasOwnProperty( this.id )
				},
				set( value ) {
					this.$store.commit( 'setIsEditing', { key: this.id, value } )
				}
			},
			isLoading: {
				get() {
					return this.$store.state.isLoading.hasOwnProperty( this.id )
				},
				set( value ) {
					this.$store.commit( 'setIsLoading', { key: this.id, value } )
				}
			},
			memberType: {
				get() {
					return this.$store.state.signupCodes[ this.wpPostId ].memberType
				},
				set( value ) {
					this.$store.commit( 'setSignupCodeProperty', {
						wpPostId: this.wpPostId,
						key: 'memberType',
						value: value
					} )
				}
			},
		},
		methods: {
			onDeleteClick() {
				let item = this
				item.isLoading = true

				item.$store.dispatch( 'submitDeleteEmailDomain', { domain: item.domain } )
					.then( item.checkStatus )
					.then( item.parseJSON )
					.then( function( data ) {
						item.isLoading = false
						item.$store.commit( 'removeEmailDomain', { domain: item.domain } )
					}, function( data ) {
						item.isLoading = false
					} )
			},
			onEditClick() {
				this.isEditing = true
			},
			onSaveClick() {
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
		props: [ 'wpPostId' ]
	}
</script>
