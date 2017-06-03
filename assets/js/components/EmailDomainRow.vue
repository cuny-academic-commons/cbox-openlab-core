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
			<a href="#" v-if="! isEditing" v-on:click="onEditClick">{{ strings.edit }}</a><a href="#" v-if="isEditing" v-on:click="onSaveClick"><strong>{{ strings.save }}</strong></a> | <a href="#" v-on:click="onDeleteClick">{{ strings.delete }}</a>
		</td>
	</tr>
</template>

<script>
	import i18nTools from '../mixins/i18nTools.js'

	export default {
		computed: {
			domain: {
				get() {
					return this.$store.state.emailDomains[ this.domainKey ]
				},
				set( value ) {
					this.$store.commit( 'setEmailDomain', { key: this.domainKey, domain: value } )
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

		methods: {
			onDeleteClick( event ) {
				event.preventDefault()

				if ( ! confirm( this.strings.deleteConfirm ) ) {
					return
				}

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
			i18nTools
		],

		props: [ 'domainKey' ]
	}
</script>
