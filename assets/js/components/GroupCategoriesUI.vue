<template>
	<div>
		<ul class="types-ui">
			<li v-for="groupCategoryName in groupCategoryNames">
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
	import i18nTools from '../mixins/i18nTools.js'

	export default {
		components: {
			AddNewEntityLink
		},

		computed: {
			groupCategoryNames() {
				return this.$store.state[ this.namesKey ]
			}
		},

		data() {
			return {
				itemsKey: 'groupCategories',
				namesKey: 'groupCategoryNames'
			}
		},

		mixins: [
			i18nTools
		],

		mounted() {
			this.$store.commit( 'setUpEntityNames', {
				itemsKey: 'groupCategories',
				namesKey: 'groupCategoryNames'
			} )
		}
	}
</script>
