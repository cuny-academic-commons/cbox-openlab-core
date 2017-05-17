<template>
	<div>
		<div class="registration-section">
			<h2>{{ strings.emailDomainWhitelist }}</h2>

			<p>{{ strings.emailDomainWhitelistLegend }}</p>

			<div class="add-email-domain">
				<label for="add-email-domain-input">{{ strings.addEmailDomain }}</label>
				<input
					id="add-email-domain-input"
					v-model="newDomain"
				>
				<button
					class="button"
					v-bind:disabled="! newDomain"
					v-on:click="onAddEmailDomainSubmit"
				>{{ strings.add }}</button>
			</div>
		</div>

		<div class="registration-section">
			<h2>{{ strings.signUpCodes }}</h2>

			<p>{{ strings.signUpCodesLegend }}</p>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue'

	export default {
		computed: {
			isLoadingAddEmailDomain: {
				get() {
					return this.$store.state.isLoading.hasOwnProperty( 'addEmailDomain' )
				},

				set( value ) {
					this.$store.commit( 'setIsLoading', { key: 'addEmailDomain', value } )
				}
			}
		},

		data() {
			return {
				newDomain: '',
				strings: CBOXOLStrings.strings
			}
		},

		methods: {
			ajaxError( p ) {
				// @todo better error handling
				console.error( p )
				throw 'Could not complete request.'
			},
			checkStatus(response) {
				if (response.status >= 200 && response.status < 300) {
					return response
				} else {
					var error = new Error(response.statusText)
					error.response = response
					throw error
				}
			},

			parseJSON(response) {
				return response.json()
			},

			onAddEmailDomainSubmit( e ) {
				// To avoid scope issues in the callback.
				let registration = this

				registration.isLoadingAddEmailDomain = true
				registration.$store.dispatch( 'submitAddEmailDomain', { domain: registration.newDomain } )
					.then( registration.checkStatus )
					.then( registration.parseJSON, registration.ajaxError )
					.then( function( data ) {
						console.log(data)
					} )


				// next:
				// - API create endpoint
				// - ping it
				// - set isLoadingAddEmailDomain = false on success
			}
		}
	}
</script>
