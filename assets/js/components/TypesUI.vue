<template>
	<EntityList
		:canAddNew="canAddNew"
		:contentComponent="contentComponent"
		:isToggleable="isToggleable"
		:itemsKey="itemsKey"
		:namesKey="namesKey"
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
