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
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			order: {
				get () {
					return this.$store.state[ this.itemsKey ][ this.slug ].settings.Order.data
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}
					this.$store.commit( 'setOrder', {
						itemsKey: this.itemsKey,
						slug: this.slug,
						value: value
					} )
				}
			}
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'entityType',
			'slug'
		]
	}
</script>
