<template>
		<autocomplete
			anchor="value"
			class="new-item-field"
			:custom-params="autocompleteParams"
			debounce="1000"
			:initValue="initValue"
			label="label"
			:on-select="onGroupSelect"
			:placeholder="placeholder"
			:url="endpoint"
		/>
</template>

<script>
	import Autocomplete from 'vue2-autocomplete-js'

	export default {
		components: {
			Autocomplete
		},
		computed: {
			placeholder() {
				return '- ' + this.strings.selectGroup + ' -'
			}
		},
		data() {
			return {
				autocompleteParams: { _wpnonce: CBOXOLStrings.nonce },
				endpoint: CBOXOLStrings.endpointBase + 'groups-search',
				initValue: this.$store.state.signupCodes[ this.wpPostId ].group.slug,
				strings: CBOXOLStrings.strings
			}
		},
		methods: {
			onGroupSelect( v ) {
				this.$emit( 'input', {
					name: v.label,
					slug: v.value
				} )
			}
		},
		props: [ 'wpPostId' ]
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
