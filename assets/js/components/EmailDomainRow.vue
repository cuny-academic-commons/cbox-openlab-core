<template>
	<tr>
		<td class="email-domains-domain">
			<template v-if="isEditing">
				<input v-model="domain">
			</template>

			<template v-else>
				{{ domain }}
			</template>
		</td>
		<td class="email-domains-actions">
			<a href="#" v-if="! isEditing" v-on:click="onEditClick">{{ strings.edit }}</a><a href="#" v-if="isEditing" v-on:click="onSaveClick">{{ strings.save }}</a> | <a href="#" v-on:click="onDeleteClick">{{ strings.delete }}</a>
		</td>
	</tr>
</template>

<script>
	export default {
		computed: {
			domain: {
				get() {
					return this.$store.state.emailDomains[ this.domainKey ]
				},
				set( value ) {
					this.$store.commit( 'setEmailDomain', { key: this.key, value: this.domain } )
				}
			},
			id: {
				get() {
					return 'domain-' + this.domainKey
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
		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		},
		methods: {
			onDeleteClick() {

			},
			onEditClick() {
				this.isEditing = true
			},
			onSaveClick() {
				let item = this

				item.$store.dispatch( 'submitEmailDomain', { domain: item.domain, key: item.key } )
					.then( item.checkStatus )
					.then( item.parseJSON )
					.then( function( data ) {
						item.isLoading = false
						console.log(data)
					}, function( data ) {
						item.isLoading = false
					} )
			}
		},
		props: ['domain', 'domainKey']
	}
</script>
