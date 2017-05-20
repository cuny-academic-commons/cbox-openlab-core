<template>
	<div class="add-signup-code">
		<input
			id="add-signup-code-input"
			v-bind:disabled="isLoading"
			v-model="newSignupCode"
		>
		<button
			class="button"
			v-bind:disabled="! newSignupCode || isLoading"
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
		mixins: [
			AjaxTools
		],
		methods: {
			onSubmit( e ) {
				// To avoid scope issues in the callback.
				let nsc = this

				this.isLoading = true
				nsc.$store.dispatch( 'submitSignupCode', { domain: nsc.newSignupCode } )
					.then( nsc.checkStatus )
					.then( nsc.parseJSON )
					.then( function( data ) {
						nsc.isLoading = false
						nsc.$store.commit( 'setSignupCode', { key: data, domain: data } )
						nsc.newSignupCode = ''
					}, function( data ) {
						nsc.isLoading = false
					} )
			},
		}
	}
</script>
