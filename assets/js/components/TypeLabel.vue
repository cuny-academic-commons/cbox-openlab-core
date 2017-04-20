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
	export default {
		data() {
			return {
				strings: CBOXOLStrings.strings,
				label: this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].label,
				description: this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].description
			}
		},

		props: ['typeSlug', 'labelSlug'],

		computed: {
			labelValue: {
				get () {
					let value = this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].value

					// Fall back on name.
					if ( 0 == value.length ) {
						value = this.$store.state.types[ this.typeSlug ].name
					}

					return value
				},
				set ( value ) {
					this.$store.commit( 'setTypeProperty', { slug: this.typeSlug, property: 'isModified', value: true } )
					this.$store.commit( 'setLabel', { typeSlug: this.typeSlug, labelSlug: this.labelSlug, value } )
				}
			}
		}
	}
</script>
