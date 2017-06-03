<template>
	<div>
		<ul class="types-ui">
			<li v-for="groupCategoryName in groupCategoryNames">
				<div is="GroupCategory" :slug="groupCategoryName"></div>
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
	import GroupCategory from './GroupCategory.vue'

	export default {
		components: {
			AddNewEntityLink,
			GroupCategory
		},

		computed: {
			groupCategoryNames() {
				return this.$store.state[ this.namesKey ]
			}
		},

		data() {
			return {
				itemsKey: 'groupCategories',
				namesKey: 'groupCategoryNames',
				strings: CBOXOLStrings.strings
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
