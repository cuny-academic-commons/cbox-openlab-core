<template>
	<div class="add-signup-code">
		<input
			class="new-item-field"
			id="add-signup-code-input"
			v-bind:disabled="isLoading"
			v-model="code"
		>

		<SignupCodeMemberTypeSelector v-model="memberTypeSlug" />

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
	import SignupCodeMemberTypeSelector from './SignupCodeMemberTypeSelector.vue'
	import SignupCodeGroupSelector from './SignupCodeGroupSelector.vue'

	export default {
		components: {
			SignupCodeGroupSelector,
			SignupCodeMemberTypeSelector
		},
		computed: {
			code: {
				get() {
					return this.$store.state.signupCodes[0].code
				},
				set( value ) {
					this.$store.commit( 'setSignupCodeProperty', {
						wpPostId: 0,
						field: 'code',
						value
					} )
				},
			},
			group: {
				get() {
					return this.$store.state.signupCodes[0].group
				},
				set( value ) {
					this.$store.commit( 'setSignupCodeProperty', {
						wpPostId: 0,
						field: 'group',
						value: value
					} )
				},
			},
			groupSlug: {
				get() {
					return this.group.slug
				}
			},
			memberTypeSlug: {
				get() {
					return this.$store.state.signupCodes[0].memberType.slug
				},
				set( value ) {
					this.$store.commit( 'setSignupCodeProperty', {
						wpPostId: 0,
						field: 'memberTypeSlug',
						value: value
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
					newGroup: this.groupSlug,
					newMemberType: this.memberTypeSlug,
					newSignupCode: this.code
				}

				nsc.$store.dispatch( 'submitSignupCode', payload )
					.then( nsc.checkStatus )
					.then( nsc.parseJSON )
					.then( function( data ) {
						nsc.isLoading = false
						nsc.$store.commit( 'setSignupCode', { key: data, domain: data } )
						nsc.code = ''
					}, function( data ) {
						nsc.isLoading = false
					} )
			},
		}
	}
</script>
