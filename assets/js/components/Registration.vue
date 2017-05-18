<template>
	<div>
		<div class="registration-section">
			<h2>{{ strings.emailDomainWhitelist }}</h2>

			<p>{{ strings.emailDomainWhitelistLegend }}</p>

			<div class="add-email-domain">
				<label for="add-email-domain-input">{{ strings.addEmailDomain }}</label>
				<input
					id="add-email-domain-input"
					v-bind:disabled="isLoadingAddEmailDomain"
					v-model="newDomain"
				>
				<button
					class="button"
					v-bind:disabled="! newDomain || isLoadingAddEmailDomain"
					v-on:click="onAddEmailDomainSubmit"
				>{{ strings.add }}</button>
			</div>

			<div class="email-domains">
				<table class="cboxol-item-table email-domains-table">
					<thead>
						<th class="email-domains-domain">{{ strings.domain }}</th>
						<th class="email-domains-action">{{ strings.action }}</th>
					</thead>

					<tbody>
						<div v-for="(emailDomain, index) in emailDomains" is="emailDomainRow" :domainKey="index"></div>
					</tbody>
				</table>
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
	import EmailDomainRow from './EmailDomainRow.vue'

	export default {
		components: {
			EmailDomainRow
		},
		computed: {
			emailDomains: {
				get() {
					return this.$store.state.emailDomains
				}
			},
			isLoadingAddEmailDomain: {
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

				this.isLoadingAddEmailDomain = true
				registration.$store.dispatch( 'submitEmailDomain', { domain: registration.newDomain } )
					.then( registration.checkStatus )
					.then( registration.parseJSON )
					.then( function( data ) {
						registration.isLoadingAddEmailDomain = false
						registration.$store.commit( 'setEmailDomain', { key: data, domain: data } )
						registration.newDomain = ''
					}, function( data ) {
						registration.isLoadingAddEmailDomain = false
					} )
			}
		}
	}
</script>
