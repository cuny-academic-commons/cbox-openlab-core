<template>
	<ul>
		<li v-for="allGroupType in allGroupTypes">
			<label v-bind:for="'associated-group-' + allGroupType.slug">
				<input
					type="checkbox"
					v-model="entityGroupTypes"
					:value="allGroupType.slug"
				/> {{ allGroupType.name }}
			</label>
		</li>
	</ul>
</template>

<script>
	import EntityTools from '../../mixins/EntityTools.js'

	export default {
		computed: {
			allGroupTypes() {
				return this.$store.state.groupTypes
			},

			entityGroupTypes: {
				get() {
					return this.$store.state[ this.itemsKey ][ this.slug ].groupTypes
				},
				set( value ) {
					this.setEntityProp( 'groupTypes', value )
				}
			}
		},

		mixins: [
			EntityTools
		],

		props: {
			entityType: {
				required: true,
				type: String
			},
			slug: {
				required: true,
				type: String
			}
		}
	}
</script>
