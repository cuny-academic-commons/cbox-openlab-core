<template>
	<div>
		<ul class="entity-list">
			<li v-for="entityName in entityNames">
				<EntityListItem
					:isToggleable="isToggleable"
					:itemsKey="itemsKey"
					:namesKey="namesKey"
					:slug="entityName"
				/>
			</li>
		</ul>

		<AddNewEntityLink
			:itemsKey="itemsKey"
			:namesKey="namesKey"
			:text="strings.addNewType"
		/>
	</div>
</template>

<script>
	import AddNewEntityLink from './AddNewEntityLink.vue'
	import EntityListItem from './EntityListItem.vue'

	export default {
		components: {
			AddNewEntityLink,
			EntityListItem
		},

		computed: {
			entityNames() {
				return this.$store.state[ this.namesKey ]
			}
		},

		data() {
			return {
				strings: CBOXOLStrings.strings
			}
		},

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: this.itemsKey,
				namesKey: this.namesKey
			} )
		},

		props: [
			'canAddNew',
			'contentComponent',
			'isToggleable',
			'itemsKey',
			'namesKey'
		]
	}
</script>
