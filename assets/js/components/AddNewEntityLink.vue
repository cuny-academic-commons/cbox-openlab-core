<template>
	<a
		class="add-new-type-toggle"
		href=""
		v-on:click="onClick"
	>
		+ {{ text }}
	</a>
</template>

<script>
	import EntityTools from '../mixins/EntityTools.js'

	export default {
		methods: {
			onClick( event ) {
				event.preventDefault()
				this.$store.commit( 'addNewEntity', {
					itemsKey: this.itemsKey,
					namesKey: this.namesKey
				} )
			}
		},

		mixins: [
			EntityTools
		],

		props: [
			'text'
		]
	}
</script>
