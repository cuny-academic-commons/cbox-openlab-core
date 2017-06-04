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
			:entityType="entityType"
			:text="strings.addNewType"
		/>
	</div>
</template>

<script>
	import AddNewEntityLink from './AddNewEntityLink.vue'
	import EntityListItem from './EntityListItem.vue'

	import EntityTools from '../mixins/EntityTools.js'
	import i18nTools from '../mixins/i18nTools.js'

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

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: this.itemsKey,
				namesKey: this.namesKey
			} )
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'entityType',
			'isToggleable', // @todo Move this to the EntityType schema
		]
	}
</script>
