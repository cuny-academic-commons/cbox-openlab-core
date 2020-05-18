<template>
	<ul>
		<li v-for="allGroupType in allGroupTypes">
			<input
				type="checkbox"
				v-model="entityGroupTypes"
				:id="idBase + allGroupType.slug"
				:value="allGroupType.slug"
			/>

			<label :for="idBase + allGroupType.slug">
				 {{ allGroupType.name }}
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
			},

			idBase() {
				return 'associated-group-' + this.slug + '-'
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
