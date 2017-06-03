<template>
	<select v-on:change="onChange" class="new-item-field" v-model="selected">
		<option value="">- {{ strings.selectMemberType }} -</option>
		<option v-for="memberType in memberTypes" v-bind:value="memberType.value">
			{{ memberType.label }}
		</option>
	</select>
</template>

<script>
	import i18nTools from '../mixins/i18nTools.js'

	export default {
		computed: {
				selected() {
					return this.$store.state.signupCodes[ this.wpPostId ].memberType.slug
				}
		},
		data() {
			return {
				memberTypes: this.$store.state.memberTypes
			}
		},
		methods: {
			onChange( $event ) {
				this.$emit( 'input', $event.target.value )
			}
		},

		mixins: [
			i18nTools
		],

		props: [ 'wpPostId' ]
	}
</script>
