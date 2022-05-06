<template>
	<div class="entity-list-container">
		<div v-if="saveInProgress" class="save-in-progress"></div>

		<draggable
			:class="listClasses"
			tag="ul"
			v-model="entityNames"
			:disabled="disableSortable"
			handle=".sortable-handle"
		>
			<li v-for="entityName in entityNames">
				<EntityListItem
					:entityType="entityType"
					:isSortable="isSortable"
					:isToggleable="isToggleable"
					:slug="entityName"
				/>
			</li>
		</draggable>

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
	import draggable from 'vuedraggable'
	import classNames from 'classnames'

	export default {
		components: {
			AddNewEntityLink,
			draggable,
			EntityListItem
		},

		computed: {
			addNewText() {
				return this.getEntityTypeProp( 'addNewPlaceholder' )
			},
			disableSortable() {
				return ! this.isSortable
			},
			entityNames: {
				get() {
					const rawVals = this.$store.state[ this.namesKey ]
					return rawVals.length ? rawVals : []
				},
				set( orderedSlugs ) {
					this.$store.commit( 'setEntityNames', {
						namesKey: this.namesKey,
						names: orderedSlugs
					} )

					this.updateEntityOrder()
				}
			},
			listClasses() {
				return classNames( {
					'entity-list': true,
					'entity-list-sortable': this.isSortable
				} )
			},
			saveInProgress: {
				get() {
					return this.$store.state.saveInProgress
				},
				set( value ) {
					this.$store.commit( 'setSaveInProgress', { value } )
				}
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
			'isSortable',
			'isToggleable', // @todo Move this to the EntityType schema
		]
	}
</script>
