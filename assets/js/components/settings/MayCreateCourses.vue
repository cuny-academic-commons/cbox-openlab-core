<template>
	<div class="cboxol-item-type-setting">
		<fieldset>
			<legend>{{ strings.mayCreateCoursesLegend }}</legend>

			<label v-bind:for="'may-create-courses-no-' + slug">
				{{ strings.no }}
				<input type="radio" value="no" v-bind:name="'may-create-courses-' + slug" v-bind:id="'may-create-courses-no-' + slug" v-model="mayCreateCourses">
			</label>

			<label v-bind:for="'may-create-courses-yes-' + slug">
				{{ strings.yes }}
				<input type="radio" value="yes" v-bind:name="'may-create-courses-' + slug" v-bind:id="'may-create-courses-yes-' + slug" v-model="mayCreateCourses">
			</label>
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
			mayCreateCourses: function() {
				return this.value ? 'yes' : 'no'
			}
		},

		props: ['slug', 'value']
	}
</script>
