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
		</div>
	</div>
</template>

<script>
	import EmailDomainRow from './EmailDomainRow.vue'
	import NewEmailDomain from './NewEmailDomain.vue'
	import NewSignupCode from './NewSignupCode.vue'

	export default {
		components: {
			EmailDomainRow,
			NewEmailDomain,
			NewSignupCode
		},
		computed: {
			emailDomains: {
				get() {
					return this.$store.state.emailDomains
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
