<template>
	<tr>
		<td class="signup-code-code">
			{{ signupCode.code }}
		</td>
		<td class="signup-code-member-type">
			{{ signupCode.memberType.name }}
		</td>
		<td class="signup-code-group">
			{{ signupCode.group.name }}
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
			id: {
				get() {
					return 'signupCode-' + this.signupCode.code
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
			}
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
		props: [ 'signupCode' ]
	}
</script>
