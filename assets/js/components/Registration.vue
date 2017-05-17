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
		data() {
			return {
				newDomain: '',
				strings: CBOXOLStrings.strings
			}
		}
	}
</script>
