<template>
	<div>
		<ul class="types-ui">
			<li v-for="typeName in typeNames">
				<div is="itemType" :slug="typeName"></div>
			</li>
		</ul>

		<a
			class="add-new-type-toggle"
			href=""
			v-if="canAddNew"
			v-on:click="addNewType"
		>
			+ {{ strings.addNewType }}
		</a>
	</div>
</template>

<script>
	import ItemType from './ItemType.vue'

	export default {
		components: {
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
