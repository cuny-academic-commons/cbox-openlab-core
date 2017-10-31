<template>
	<div>
		<ul class="entity-list">
			<li v-for="entityName in entityNames">
				<EntityListItem
					:entityType="entityType"
					:isToggleable="isToggleable"
					:slug="entityName"
				/>
			</li>
		</ul>

		<AddNewEntityLink
			v-if="supportsAdding"
			:entityType="entityType"
			:text="addNewText"
		/>
	</div>
</template>

<script>
	import AddNewEntityLink from './AddNewEntityLink.vue'
	import EntityListItem from './EntityListItem.vue'

	import EntityTools from '../mixins/EntityTools.js'

	export default {
		components: {
			AddNewEntityLink,
			EntityListItem
		},

		computed: {
			addNewText() {
				return this.getEntityTypeProp( 'addNewPlaceholder' )
			},
			entityNames() {
				return this.$store.state[ this.namesKey ]
			},
			supportsAdding() {
				return 'groupType' !== this.entityType
			}
		},

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: this.itemsKey,
				namesKey: this.namesKey
			} )
		},

		mixins: [
			EntityTools
		],

		props: [
			'entityType',
			'isToggleable', // @todo Move this to the EntityType schema
		]
	}
</script>
