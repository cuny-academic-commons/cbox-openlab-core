<template>
	<div class="cboxol-item-type-label">
		<label v-bind:for="typeSlug + '-label-' + labelSlug">{{ label }}</label>
		<input
			v-bind:id="typeSlug + '-label-' + labelSlug"
			v-model="labelValue"
		>
		{{ description }}
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
					return this.$store.state.types[ this.typeSlug ].labels[ this.labelSlug ].value
				},
				set ( value ) {
					this.$store.commit( 'setLabel', { typeSlug: this.typeSlug, labelSlug: this.labelSlug, value } )
				}
			}
		}
	}
</script>
