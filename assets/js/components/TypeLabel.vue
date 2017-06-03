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
		computed: {
			entityIsModified: {
				get() {
					return this.$store.state.types[ this.typeSlug ].isModified
				},

				set( value ) {
					this.$store.commit( 'setEntityProperty', {
						itemsKey: 'types',
						property: 'isModified',
						slug: this.typeSlug,
						value: value
					} )
				}
			},

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
					if ( ! this.entityIsModified ) {
						this.entityIsModified = true
					}

					this.$store.commit( 'setLabel', { typeSlug: this.typeSlug, labelSlug: this.labelSlug, value } )
				}
			}
		},

		data() {
			return {
				description: this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].description,
				label: this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].label,
				strings: CBOXOLStrings.strings
			}
		},

		props: [
			'labelSlug',
			'typeSlug'
		]
	}
</script>
