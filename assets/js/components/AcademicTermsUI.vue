<template>
	<EntityList
		:canAddNew="canAddNew"
		:isSortable="true"
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

		data() {
			return {
				canAddNew: true,
				entityType: 'academicTerm',
				isSortable: false,
				isToggleable: false,
			}
		},

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'academicTerms',
				namesKey: 'academicTermNames'
			} )
		}
	}
</script>
