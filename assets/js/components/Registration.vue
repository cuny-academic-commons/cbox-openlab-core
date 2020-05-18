<template>
	<div>
		<div class="registration-section">
			<h2>{{ strings.emailDomainWhitelist }}</h2>

			<p>{{ strings.emailDomainWhitelistLegend }}</p>

			<NewEmailDomain />

			<div class="email-domains">
				<template v-if="hasEmailDomains">
					<table class="cboxol-item-table email-domains-table">
						<thead>
							<th class="email-domains-domain">{{ strings.domain }}</th>
							<th class="email-domains-action">{{ strings.action }}</th>
						</thead>

						<tbody>
							<div v-for="(emailDomain, index) in emailDomains" is="emailDomainRow" :domainKey="index"></div>
						</tbody>
					</table>
				</template>

				<template v-else>
					{{ strings.noEmailDomains }}
				</template>
			</div>

		</div>

		<div class="registration-section">
			<h2>{{ strings.signUpCodes }}</h2>

			<p>{{ strings.signUpCodesLegend }}</p>

			<NewSignupCode />

			<div class="signup-codes">
				<template v-if="hasSignupCodes">
					<table class="cboxol-item-table signup-codes-table">
						<thead>
							<th class="signup-domains-code">{{ strings.code }}</th>
							<th class="signup-domains-member-type">{{ strings.memberType }}</th>
							<th class="signup-domains-group">{{ strings.group }}</th>
							<th class="signup-domains-action">{{ strings.action }}</th>
						</thead>

						<tbody>
							<div v-for="(signupCode, wpPostId) in signupCodes" is="signupCodeRow" :wpPostId="wpPostId"></div>
						</tbody>
					</table>
				</template>

				<template v-else>
					{{ strings.noSignupCodes }}
				</template>
			</div>
		</div>

		<div class="registration-section form-customization-section">
			<h2>{{ strings.formCustomization }}</h2>

			<p>{{ strings.formCustomizationLegend }}</p>

			<table class="form-table">
				<tr class="confirmation-text">
					<th><label for="confirmation-text-entry">{{ strings.confirmationText }}</label></th>
					<td>
						<input
							class="confirmation-text-entry"
							id="confirmation-text-entry"
							type="text"
							v-model="confirmationText"
						/>
						<p class="description">{{ strings.confirmationTextLegend }}</p>
					</td>
				</tr>
			</table>

			<button
				class="button-primary"
				v-on:click="onSaveClick"
			>{{ strings.formCustomizationSave }}</button>
		</div>
	</div>
</template>

<script>
	import EmailDomainRow from './EmailDomainRow.vue'
	import NewEmailDomain from './NewEmailDomain.vue'
	import NewSignupCode from './NewSignupCode.vue'
	import SignupCodeRow from './SignupCodeRow.vue'

	import i18nTools from '../mixins/i18nTools.js'

	export default {
		components: {
			EmailDomainRow,
			NewEmailDomain,
			NewSignupCode,
			SignupCodeRow
		},

		computed: {
			emailDomains() {
				return this.$store.state.emailDomains
			},
			hasEmailDomains() {
				return 0 < Object.keys( this.emailDomains ).length
			},
			hasSignupCodes() {
				return 0 < Object.keys( this.signupCodes ).length
			},
			signupCodes() {
				let codes = {}
				for ( var k in this.$store.state.signupCodes ) {
					if ( 0 < this.$store.state.signupCodes[ k ].wpPostId ) {
						codes[ k ] = this.$store.state.signupCodes[ k ]
					}
				}
				return codes
			}
		},

		data() {
			return {
				confirmationText: ''
			}
		},

		methods: {
			onSaveClick( e ) {
				// To avoid scope issues in the callback.
				let nsc = this

				this.isLoading = true

				const payload = {
					settings: {
						confirmationText: this.confirmationText
					}
				}

				nsc.$store.dispatch( 'submitRegistrationFormSettings', payload )
					.then( nsc.checkStatus )
					.then( nsc.parseJSON )
					.then( function( data ) {
						nsc.isLoading = false
						return;
						nsc.$store.commit( 'setSignupCode', { key: data.wpPostId, signupCode: data } )
						nsc.code = ''
						nsc.group = { name: '', slug: '' }
						nsc.memberTypeSlug = ''
					}, function( data ) {
						nsc.isLoading = false
					} )
			}
		},

		mixins: [
			i18nTools
		],

		mounted() {
			this.confirmationText = this.$store.state.registrationFormSettings.confirmationText
		}
	}
</script>
