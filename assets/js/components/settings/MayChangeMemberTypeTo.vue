<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
			<div class="cboxol-setting-label">
				<legend>{{ strings.mayChangeMemberTypeToLegend }}</legend>
			</div>

			<div class="cboxol-setting-content">
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
			</div>

		</fieldset>
	</div>
</template>

<script>
	import EntityTools from '../../mixins/EntityTools.js'

	export default {
		computed: {
			allTypes: function() {
				var retval = {}, key
				for ( key in this.$store.state.types[ this.slug ].settings.MayChangeMemberTypeTo.data.allTypes ) {
					if ( key !== this.slug ) {
						retval[ key ] = this.$store.state.types[ this.slug ].settings.MayChangeMemberTypeTo.data.allTypes[ key ]
					}
				}

				return retval
			},
			selectableTypes: {
				get () {
					return this.$store.state.types[ this.slug ].settings.MayChangeMemberTypeTo.data.selectableTypes
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}

					this.$store.commit( 'setSelectableTypes', { slug: this.slug, selectableTypes: value } )
				}
			}
		},

		data() {
			return {
				itemsKey: 'types',
				strings: CBOXOLStrings.strings
			}
		},

		mixins: [
			EntityTools
		],

		props: [
			'slug'
		]
	}
</script>
