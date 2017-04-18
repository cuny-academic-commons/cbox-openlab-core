<template>
	<div>
		<h3>Hello world</h3>
		<ul class="types-ui">
			<li v-for="type in types">
				<!-- for testing - teh is entire data object from store -->
				<div is="itemType" :slug="type.slug"></div>
			</li>
		</ul>
	</div>
</template>

<script>
	import ItemType from './ItemType.vue'

	export default {
		components: {
			'itemType': ItemType
		},
		computed: {
			types() {
				return this.$store.state.types
			}
		}
	}
</script>
