<template>
	<ul class="academic-unit-parent-selector">
		<li v-for="unit in unitsOfType">
			<input
				:name="checkboxName( unit )"
				type="checkbox"
				:value="unit.slug"
			/>

			<label
				:for="checkboxName( unit )"
			/>{{ unit.name }}</label>
		</li>
	</ul>
</template>

<script>

	export default {
		computed: {
			unitsOfType() {
				let units = {}
				let currentUnit

				for ( let unitSlug in this.$store.state.academicUnits ) {
					if ( '_new-' === unitSlug.substr( 0, 5 ) ) {
						continue
					}

					currentUnit = this.$store.state.academicUnits[ unitSlug ]

					if ( currentUnit.type === this.academicUnitTypeSlug ) {
						units[ unitSlug ] = currentUnit
					}
				}

				return units
			}
		},

		methods: {
			checkboxName( unit ) {
				return this.thisUnitSlug + '-parent-' + unit.slug
			}
		},

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
