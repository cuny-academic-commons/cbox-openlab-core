<template>
	<EntityList
		:canAddNew="canAddNew"
		:contentComponent="contentComponent"
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
			canAddNew() {
				return 'member' === this.objectType
			},
			entityType() {
				return 'member' === this.objectType ? 'memberType' : 'groupType'
			}
		},
		data() {
			return {
				contentComponent: 'EntityContentItemType',
				isToggleable: true,
				itemsKey: 'types',
				namesKey: 'typeNames',
				objectType: this.$store.state.objectType
			}
		}
	}
</script>
