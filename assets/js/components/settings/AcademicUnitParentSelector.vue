<template>
	<select class="academic-unit-parent-selector" v-model="parentSlug">
		<option value="">- {{ strings.none }} -</option>
		<option v-for="unit in unitsOfType"
			v-bind:value="unit.slug"
		>
			{{ unit.name }}
		</option>
	</select>
</template>

<script>
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			parentSlug: {
				get() {
					return this.$store.state.academicUnits[ this.thisUnitSlug ].parent
				},
				set( value ) {
					this.$store.commit( 'setEntityProperty', {
						itemsKey: 'academicUnits',
						property: 'parent',
						slug: this.thisUnitSlug,
						value
					} );
				}
			},

			unitsOfType() {
				let units = {}
				let currentUnit

				for ( let i in this.$store.state.academicUnitNames ) {
					currentUnit = this.$store.state.academicUnits[ this.$store.state.academicUnitNames[ i ] ]

					if ( ! currentUnit.hasOwnProperty( 'slug' ) ) {
						console.log( currentUnit )
					}

					if ( '_new-' === currentUnit.slug.substr( 0, 5 ) ) {
						continue
					}

					if ( currentUnit.type === this.academicUnitTypeSlug ) {
						units[ currentUnit.slug ] = currentUnit
					}
				}

				return units
			}
		},

		data() {
			return {
				checkboxValue: false
			}
		},

		methods: {
			checkboxName( unit ) {
				return this.thisUnitSlug + '-parent-' + unit.slug
			}
		},

		mixins: [
			i18nTools
		],


		props: {
			academicUnitTypeSlug: {
				required: true
			},
			thisUnitSlug: {
				required: true
			}
		}
	}
</script>
