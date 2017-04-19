<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
		  <legend>{{ strings.mayChangeMemberTypeToLegend }}</legend>
			{{ selectableTypes }}

			<ul class="cboxol-item-type-setting-checkbox-list">
				<li v-for="type in allTypes">
					<input
						type="checkbox"
						v-bind:value="type.id"
						v-bind:id="slug + '-may-change-member-type-to-' + type.slug"
						v-model="selectableTypes"
						>
					<label v-bind:for="slug + '-may-change-member-type-to-' + type.slug">{{ type.name }}</label>
				</li>
			</ul>

		</fieldset>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				allTypes: this.$store.state.types[ this.slug ].settings.MayChangeMemberTypeTo.data.allTypes,
				strings: CBOXOLStrings.strings
			}
		},

		computed: {
			selectableTypes: {
				get () {
					return this.$store.state.types[ this.slug ].settings.MayChangeMemberTypeTo.data.selectableTypes
				},
				set ( value ) {
					this.$store.commit( 'setSelectableTypes', { slug: this.slug, selectableTypes: value } )
				}
			}
		},

		props: ['slug', 'data']
	}
</script>
