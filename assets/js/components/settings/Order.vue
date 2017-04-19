<template>
	<div class="cboxol-item-type-setting">
		<div class="cboxol-setting-label">
			<label v-bind:for="'order-' + slug">{{ strings.orderLegend }}</label>
			<p class="description">{{ strings.orderDescription }}</p>
		</div>

		<div class="cboxol-setting-content">
			<input
				v-bind:id="'order-' + slug"
				v-model='order'
			>
		</div>
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
					this.$store.commit( 'setTypeProperty', { slug: this.slug, property: 'isModified', value: true } )
					this.$store.commit( 'setOrder', { slug: this.slug, value: value } )
				}
			}
		},

		props: ['slug']
	}
</script>
