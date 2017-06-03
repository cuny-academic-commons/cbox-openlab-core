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
	import EntityTools from '../../mixins/EntityTools.js'

	export default {
		computed: {
			order: {
				get () {
					return this.$store.state.types[ this.slug ].settings.Order.data
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}
					this.$store.commit( 'setOrder', { slug: this.slug, value: value } )
				}
			}
		},

		data() {
			return {
				itemsKey: 'types',
				strings: CBOXOLStrings.strings
			}
		},

		mixins: [
			EntityTools
		],

		props: [
			'slug'
		]
	}
</script>
