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
				entityType: 'groupCategory',
				isToggleable: false,
			}
		},

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'groupCategories',
				namesKey: 'groupCategoryNames'
			} )
		}
	}
</script>
