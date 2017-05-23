<template>
	<div class="add-signup-code">
		<input
			class="new-item-field"
			id="add-signup-code-input"
			v-bind:disabled="isLoading"
			v-model="newSignupCode"
		>

		<SignupCodeMemberTypeSelector v-model="newMemberType" />

		<SignupCodeGroupSelector v-model="newGroup" />

		<button
			class="button"
			v-bind:disabled="! newSignupCode || isLoading"
			v-on:click="onSubmit"
		>{{ strings.add }}</button>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import SignupCodeMemberTypeSelector from './SignupCodeMemberTypeSelector.vue'
	import SignupCodeGroupSelector from './SignupCodeGroupSelector.vue'

	export default {
		components: {
			SignupCodeGroupSelector,
			SignupCodeMemberTypeSelector
		},
		computed: {
			newGroup: {
				get() {
					return this.$store.state.newSignupCode.groupSlug
				},
				set( value ) {
					this.$store.commit( 'setFormValue', {
						form: 'newSignupCode',
						field: 'groupSlug',
						value
					} )
				},
			},
			newMemberType: {
				get() {
					return this.$store.state.newSignupCode.memberType
				},
				set( value ) {
					this.$store.commit( 'setFormValue', {
						form: 'newSignupCode',
						field: 'memberType',
						value
					} )
				},
			},
			newSignupCode: {
				get() {
					return this.$store.state.newSignupCode.code
				},
				set( value ) {
					this.$store.commit( 'setFormValue', {
						form: 'newSignupCode',
						field: 'code',
						value
					} )
				},
			},
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
				strings: CBOXOLStrings.strings
			}
		},
		mixins: [
			AjaxTools
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
					newGroup: this.newGroup,
					newMemberType: this.newMemberType,
					newSignupCode: this.newSignupCode
				}

				nsc.$store.dispatch( 'submitSignupCode', payload )
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
