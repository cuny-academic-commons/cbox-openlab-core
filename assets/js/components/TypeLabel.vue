<template>
	<div class="cboxol-item-type-label">
		<div class="cboxol-setting-label">
			<label v-bind:for="typeSlug + '-label-' + labelSlug">{{ label }}</label>
		</div>

		<div class="cboxol-setting-content">
			<input
				v-bind:id="typeSlug + '-label-' + labelSlug"
				v-model="labelValue"
			>
			<p class="description">{{ description }}</p>
		</div>
	</div>
</template>

<script>
	import i18nTools from '../mixins/i18nTools.js'
	import EntityTools from '../mixins/EntityTools.js'

	export default {
		computed: {
			description() {
				return this.$store.state[ this.itemsKey ][ this.typeSlug ].labels[ this.labelSlug ].description
			},

			label() {
				return this.$store.state[ this.itemsKey ][ this.typeSlug ].labels[ this.labelSlug ].label
			},

			labelValue: {
				get () {
					let value = this.$store.state[ this.itemsKey ][ this.typeSlug ].labels[ this.labelSlug ].value

					// Fall back on name.
					if ( 0 == value.length ) {
						value = this.$store.state[ this.itemsKey ][ this.typeSlug ].name
					}

					return value
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}

					this.$store.commit( 'setLabel', {
						itemsKey: this.itemsKey,
						labelSlug: this.labelSlug,
						typeSlug: this.typeSlug,
						value
					} )
				}
			},

			// Needed for EntityTools
			slug() {
				return this.typeSlug
			}
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'entityType',
			'labelSlug',
			'typeSlug'
		]
	}
</script>
