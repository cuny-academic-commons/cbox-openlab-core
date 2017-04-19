<template>
	<div class="cboxol-item-type-setting">
		<label v-bind:for="'order-' + slug">{{ strings.orderLegend }}</label>

		<input
			v-bind:id="'order-' + slug"
			v-model='order'
		>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		},

		computed: {
			order: {
				get () {
					return this.$store.state.types[ this.slug ].settings.Order.data
				},
				set ( value ) {
					this.$store.commit( 'setOrder', { slug: this.slug, value: value } )
				}
			}
		},

		props: ['slug']
	}
</script>
