<template>
	<div>
		<ul class="types-ui">
			<li v-for="type in types">
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
		},
		data() {
			return {
				objectType: CBOXOL_ObjectType
			}
		}
	}
</script>
