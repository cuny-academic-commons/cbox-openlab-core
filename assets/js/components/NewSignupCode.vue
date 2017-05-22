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

		<autocomplete
			anchor="value"
			class="new-item-field"
			:custom-params="autocompleteParams"
			debounce="1000"
			label="label"
			:on-select="onGroupSelect"
			:url="endpoint"
			v-model="newGroup"
		/>

		<button
			class="button"
			v-bind:disabled="! newSignupCode || isLoading"
			v-on:click="onSubmit"
		>{{ strings.add }}</button>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import Autocomplete from 'vue2-autocomplete-js'

	export default {
		components: {
			Autocomplete
		},
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
				autocompleteParams: { _wpnonce: CBOXOLStrings.nonce },
				endpoint: CBOXOLStrings.endpointBase + 'groups-search',
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

<style>
.transition, .autocomplete, .showAll-transition, .autocomplete ul, .autocomplete ul li a{
  transition:all 0.3s ease-out;
  -moz-transition:all 0.3s ease-out;
  -webkit-transition:all 0.3s ease-out;
  -o-transition:all 0.3s ease-out;
}

.autocomplete ul{
  font-family: sans-serif;
  position: absolute;
  list-style: none;
  background: #f8f8f8;
  padding: 10px 0;
  margin: 0;
  display: inline-block;
  min-width: 15%;
  margin-top: 10px;
}

.autocomplete ul:before{
  content: "";
  display: block;
  position: absolute;
  height: 0;
  width: 0;
  border: 10px solid transparent;
  border-bottom: 10px solid #f8f8f8;
  left: 46%;
  top: -20px
}

.autocomplete ul li a{
  text-decoration: none;
  display: block;
  background: #f8f8f8;
  color: #2b2b2b;
  padding: 5px;
  padding-left: 10px;
}

.autocomplete ul li a:hover, .autocomplete ul li.focus-list a{
  color: white;
  background: #2F9AF7;
}

.autocomplete ul li a span{
  display: block;
  margin-top: 3px;
  color: grey;
  font-size: 13px;
}

.autocomplete ul li a:hover span, .autocomplete ul li.focus-list a span{
  color: white;
}

.showAll-transition{
  opacity: 1;
  height: 50px;
  overflow: hidden;
}

.showAll-enter{
  opacity: 0.3;
  height: 0;
}

.showAll-leave{
  display: none;
}
</style>
