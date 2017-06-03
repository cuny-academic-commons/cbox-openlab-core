<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
			<div class="cboxol-setting-label">
				<legend>{{ strings.mayCreateCoursesLegend }}</legend>
			</div>

			<div class="cboxol-setting-content">
				<label v-bind:for="'may-create-courses-no-' + slug">
					{{ strings.no }}
					<input type="radio" value="no" v-bind:name="'may-create-courses-' + slug" v-bind:id="'may-create-courses-no-' + slug" v-model="mayCreateCourses">
				</label>

				<label v-bind:for="'may-create-courses-yes-' + slug">
					{{ strings.yes }}
					<input type="radio" value="yes" v-bind:name="'may-create-courses-' + slug" v-bind:id="'may-create-courses-yes-' + slug" v-model="mayCreateCourses">
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
			mayCreateCourses: {
				get () {
					return this.$store.state.types[ this.slug ].settings.MayCreateCourses.data ? 'yes' : 'no'
				},
				set ( value ) {
					if ( ! this.isModified ) {
						this.isModified = true
					}

					this.$store.commit( 'setMayCreateCourses', { slug: this.slug, value: value } )
				}
			}
		},

		data() {
			return {
				itemsKey: 'types'
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
