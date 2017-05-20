<template>
	<div class="add-signup-code">
		<input
			class="new-item-field"
			id="add-signup-code-input"
			v-bind:disabled="isLoading"
			v-model="newSignupCode"
		>

		<select v-model="newMemberType" class="new-item-field">
			<option value="">- {{ strings.selectMemberType }} -</option>
			<option v-for="memberType in memberTypes" v-bind:value="memberType.value">
				{{ memberType.label }}
			</option>
		</select>

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
			}
		},
		data() {
			return {
				memberTypes: this.$store.state.memberTypes,
				newGroup: '',
				newMemberType: '',
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
