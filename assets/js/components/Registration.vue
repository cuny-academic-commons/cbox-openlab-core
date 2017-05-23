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

			<NewSignupCode />

			<div class="signup-codes">
				<table class="cboxol-item-table signup-codes-table">
					<thead>
						<th class="signup-domains-code">{{ strings.code }}</th>
						<th class="signup-domains-member-type">{{ strings.memberType }}</th>
						<th class="signup-domains-group">{{ strings.group }}</th>
						<th class="signup-domains-action">{{ strings.action }}</th>
					</thead>

					<tbody>
						<div v-for="signupCode in signupCodes" is="signupCodeRow" :signupCode="signupCode"></div>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
	import EmailDomainRow from './EmailDomainRow.vue'
	import NewEmailDomain from './NewEmailDomain.vue'
	import NewSignupCode from './NewSignupCode.vue'
	import SignupCodeRow from './SignupCodeRow.vue'

	export default {
		components: {
			EmailDomainRow,
			NewEmailDomain,
			NewSignupCode,
			SignupCodeRow
		},
		computed: {
			emailDomains: {
				get() {
					return this.$store.state.emailDomains
				}
			},
			signupCodes: {
				get() {
					return this.$store.state.signupCodes
				}
			},
		},

		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		}
	}
</script>
