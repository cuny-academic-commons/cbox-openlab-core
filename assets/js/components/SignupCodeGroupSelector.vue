<template>
		<autocomplete
			anchor="value"
			class="new-item-field"
			:custom-params="autocompleteParams"
			debounce="1000"
			label="label"
			:on-select="onGroupSelect"
			:url="endpoint"
		/>
</template>

<script>
	import Autocomplete from 'vue2-autocomplete-js'

	export default {
		components: {
			Autocomplete
		},
		data() {
			return {
				autocompleteParams: { _wpnonce: CBOXOLStrings.nonce },
				endpoint: CBOXOLStrings.endpointBase + 'groups-search'
			}
		},
		methods: {
			onGroupSelect( v ) {
				this.$emit( 'input', v.value )
			}
		}
	}
</script>
