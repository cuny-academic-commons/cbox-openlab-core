<template>
	<div>
		<ul class="types-ui">
			<li v-for="typeName in typeNames">
				<div is="itemType" :slug="typeName"></div>
			</li>
		</ul>

		<a href="" v-on:click="addNewType" class="add-new-type-toggle">+ {{ strings.addNewType }}</a>
	</div>
</template>

<script>
	import ItemType from './ItemType.vue'

	export default {
		components: {
			'itemType': ItemType
		},
		computed: {
			typeNames: {
				get () {
					return this.$store.state.typeNames
				}
			}
		},
		data() {
			return {
				strings: CBOXOLStrings.strings,
				objectType: CBOXOL_ObjectType
			}
		},
		methods: {
			addNewType( event ) {
				event.preventDefault()
				this.$store.commit( 'addNewType' )
			}
		}
	}
</script>
