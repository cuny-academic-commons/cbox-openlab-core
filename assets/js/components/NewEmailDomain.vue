<template>
	<div class="add-email-domain">
		<label for="add-email-domain-input">{{ strings.addEmailDomain }}</label>
		<input
			id="add-email-domain-input"
			v-bind:disabled="isLoading"
			v-model="newDomain"
		>
		<button
			class="button"
			v-bind:disabled="! newDomain || isLoading"
			v-on:click="onSubmit"
		>{{ strings.add }}</button>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'

	export default {
		computed: {
			isLoading: {
				get() {
					return this.$store.state.isLoading.hasOwnProperty( 'addEmailDomain' )
				},

				set( value ) {
					this.$store.commit( 'setIsLoading', { key: 'addEmailDomain', value } )
				}
			},
		},
		data() {
			return {
				newDomain: '',
				newSignupCode: '',
				strings: CBOXOLStrings.strings
			}
		},
		mixins: [
			AjaxTools
		],
		methods: {
			onSubmit( e ) {
				// To avoid scope issues in the callback.
				let registration = this

				this.isLoading = true
				registration.$store.dispatch( 'submitEmailDomain', { domain: registration.newDomain } )
					.then( registration.checkStatus )
					.then( registration.parseJSON )
					.then( function( data ) {
						registration.isLoading = false
						registration.$store.commit( 'setEmailDomain', { key: data, domain: data } )
						registration.newDomain = ''
					}, function( data ) {
						registration.isLoading = false
					} )
			},
		}
	}
</script>
