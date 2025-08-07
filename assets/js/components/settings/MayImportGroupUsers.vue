<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
			<div class="cboxol-setting-label">
				<legend>{{ strings.mayImportGroupUsersLegend }}</legend>
			</div>

			<div class="cboxol-setting-content">
				<label v-bind:for="'may-import-group-users-no-' + slug">
					{{ strings.no }}
					<input type="radio" value="no" v-bind:name="'may-import-group-users-' + slug" v-bind:id="'may-import-group-users-no-' + slug" v-model="mayImportGroupUsers">
				</label>

				<label v-bind:for="'may-import-group-users-yes-' + slug">
					{{ strings.yes }}
					<input type="radio" value="yes" v-bind:name="'may-import-group-users-' + slug" v-bind:id="'may-import-group-users-yes-' + slug" v-model="mayImportGroupUsers">
				</label>
			</div>
		</fieldset>
	</div>
</template>

<script>
	import EntityTools from '../../mixins/EntityTools.js'
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			mayImportGroupUsers: {
				get () {
					return this.$store.state.types[ this.slug ].settings.MayImportGroupUsers.data ? 'yes' : 'no'
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}

					this.$store.commit( 'setMayImportGroupUsers', { slug: this.slug, value: value } )
				}
			}
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'slug'
		]
	}
</script>
