<template>
	<EntityList
		:canAddNew="canAddNew"
		:isToggleable="isToggleable"
		:entityType="entityType"
	/>
</template>

<script>
	import EntityList from './EntityList.vue'

	export default {
		components: {
			EntityList,
		},

		computed: {
			groupCategoryNames() {
				return this.$store.state[ this.namesKey ]
			}
		},

		data() {
			return {
				canAddNew: true,
				entityType: 'academicUnitType',
				isToggleable: false,
			}
		},

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'academicUnits',
				namesKey: 'academicUnitNames'
			} )

			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'academicUnitTypes',
				namesKey: 'academicUnitTypeNames'
			} )
		}
	}
</script>
