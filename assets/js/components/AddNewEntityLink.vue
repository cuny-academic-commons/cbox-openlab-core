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
		props: [
			'itemsKey',
			'namesKey',
			'text'
		]
	}
</script>
