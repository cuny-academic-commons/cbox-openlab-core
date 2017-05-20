<template>
	<div>
		<div class="registration-section">
			<h2>{{ strings.emailDomainWhitelist }}</h2>

			<p>{{ strings.emailDomainWhitelistLegend }}</p>

			<NewEmailDomain />

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
	import EmailDomainRow from './EmailDomainRow.vue'
	import NewEmailDomain from './NewEmailDomain.vue'

	export default {
		components: {
			EmailDomainRow,
			NewEmailDomain
		},
		computed: {
			emailDomains: {
				get() {
					return this.$store.state.emailDomains
				}
			},
			isLoadingSignupCode: {
				get() {
					return this.$store.state.isLoading.hasOwnProperty( 'addSignupCode' )
				},

				set( value ) {
					this.$store.commit( 'setIsLoading', { key: 'addSignupCode', value } )
				}
			},
		},

		data() {
			return {
				newSignupCode: '',
				strings: CBOXOLStrings.strings
			}
		},

		methods: {
			onAddSignupCodeSubmit( e ) {
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
