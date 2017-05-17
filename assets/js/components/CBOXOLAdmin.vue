<template>
	<div v-bind:is="subapp"></div>
</template>

<script>
	import Vue from 'vue'
	import Vuex from 'vuex'
	import fetch from 'isomorphic-fetch'
	import TypesUI from './TypesUI.vue'

	export default {
		components: {
			'TypesUI': TypesUI
		},
		computed: {
			subapp: function() {
				return this.$store.state.subapp
			}
		}
	}
</script>
