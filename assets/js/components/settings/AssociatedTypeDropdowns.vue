<template>
	<ul class="associated-type-dropdowns">
		<li v-for="type in allTypes">
			<label v-bind:for="associatedType + '-' + type.value">{{ type.label }}</label>
			<AssociatedTypeDropdown
				:associatedType="associatedType"
				:associatedTypeSlug="type.value"
				:entityType="entityType"
				:slug="slug"
			/>
		</li>
	</ul>
</template>

<script>
	import AssociatedTypeDropdown from './AssociatedTypeDropdown.vue'

	export default {
		components: {
			AssociatedTypeDropdown
		},

		computed: {
			allTypes() {
				return this.$store.state[ this.associatedType ]
			}
		},

		props: [
			'associatedType',
			'entityType',
			'slug'
		]
	}
</script>
