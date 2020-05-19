<template>
	<ul class="associated-type-dropdowns">
		<li v-for="type in allTypes">
			<label v-bind:for="idBase + type.value">{{ type.label }}</label>
			<AssociatedTypeDropdown
				:associatedType="associatedType"
				:associatedTypeSlug="type.value"
				:entityType="entityType"
				:fieldId="idBase + type.value"
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
			},

			idBase() {
				return 'associated-item-type-' + this.slug + '-' + this.associatedType + '-' + this.entityType + '-'
			}
		},

		props: [
			'associatedType',
			'entityType',
			'slug'
		]
	}
</script>
