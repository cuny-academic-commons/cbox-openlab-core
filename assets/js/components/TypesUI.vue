<template>

	<div>
		<ul class="types-ui">
			<li v-for="typeName in typeNames">
				<div is="itemType" :slug="typeName"></div>
			</li>
		</ul>

		<AddNewEntityLink
			:itemsKey="itemsKey"
			:namesKey="namesKey"
			:text="strings.addNewType"
			v-if="canAddNew"
		/>
	</div>
</template>

<script>
	import AddNewEntityLink from './AddNewEntityLink.vue'
	import ItemType from './ItemType.vue'

	export default {
		components: {
			AddNewEntityLink,
			'itemType': ItemType
		},
		computed: {
			canAddNew: function() {
				return 'member' === this.objectType
			},
			typeNames: {
				get () {
					return this.$store.state.typeNames
				}
			}
		},
		data() {
			return {
				itemsKey: 'types',
				namesKey: 'typeNames',
				objectType: this.$store.state.objectType,
				strings: CBOXOLStrings.strings
			}
		},
		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'types',
				namesKey: 'typeNames'
			} )
		}
	}
</script>
