<template>
	<div class="add-signup-code">
		<input
			class="new-item-field"
			id="add-signup-code-input"
			v-bind:disabled="isLoading"
			v-model="code"
		>

		<SignupCodeMemberTypeSelector v-model="memberTypeSlug" :wpPostId="wpPostId" />

		<SignupCodeGroupSelector v-model="group" />

		<button
			class="button"
			v-bind:disabled="! code || isLoading"
			v-on:click="onSubmit"
		>{{ strings.add }}</button>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import SignupCodeTools from '../mixins/SignupCodeTools.js'
	import SignupCodeMemberTypeSelector from './SignupCodeMemberTypeSelector.vue'
	import SignupCodeGroupSelector from './SignupCodeGroupSelector.vue'

	export default {
		components: {
			SignupCodeGroupSelector,
			SignupCodeMemberTypeSelector
		},
		data() {
			return {
				strings: CBOXOLStrings.strings,
				wpPostId: 0
			}
		},
		mixins: [
			AjaxTools,
			SignupCodeTools
		],
		methods: {
			onGroupSelect( v ) {
				this.newGroup = v.value
			},
			onSubmit( e ) {
				// To avoid scope issues in the callback.
				let nsc = this

				this.isLoading = true

				const payload = {
					newGroup: this.groupSlug,
					newMemberType: this.memberTypeSlug,
					newSignupCode: this.code
				}

				nsc.$store.dispatch( 'submitSignupCode', payload )
					.then( nsc.checkStatus )
					.then( nsc.parseJSON )
					.then( function( data ) {
						nsc.isLoading = false
						nsc.$store.commit( 'setSignupCode', { key: data.wpPostId, signupCode: data } )
						nsc.code = ''
						nsc.group = { name: '', slug: '' }
						nsc.memberTypeSlug = ''
					}, function( data ) {
						nsc.isLoading = false
					} )
			},
		}
	}
</script>
