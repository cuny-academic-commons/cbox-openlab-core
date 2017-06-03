<template>
	<div v-bind:is="subapp"></div>
</template>

<script>
	import Vue from 'vue'
	import GroupCategoriesUI from './GroupCategoriesUI.vue'
	import Registration from './Registration.vue'
	import TypesUI from './TypesUI.vue'

	export default {
		components: {
			GroupCategoriesUI,
			Registration,
			TypesUI
		},
		computed: {
			subapp: function() {
				return this.$store.state.subapp
			}
		}
	}
</script>
