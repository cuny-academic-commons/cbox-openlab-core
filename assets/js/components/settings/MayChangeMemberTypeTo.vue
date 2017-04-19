<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
		  <legend>{{ strings.mayChangeMemberTypeToLegend }}</legend>

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
				strings: CBOXOLStrings.strings
			}
		},

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
					this.$store.commit( 'setSelectableTypes', { slug: this.slug, selectableTypes: value } )
				}
			}
		},

		props: ['slug', 'data']
	}
</script>
