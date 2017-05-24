<template>
	<select v-on:change="onChange" class="new-item-field" v-model="selected">
		<option value="">- {{ strings.selectMemberType }} -</option>
		<option v-for="memberType in memberTypes" v-bind:value="memberType.value">
			{{ memberType.label }}
		</option>
	</select>
</template>

<script>
	export default {
		computed: {
				selected() {
					return this.$store.state.signupCodes[ this.wpPostId ].memberType.slug
				}
		},
		data() {
			return {
				memberTypes: this.$store.state.memberTypes,
				strings: CBOXOLStrings.strings
			}
		},
		methods: {
			onChange( $event ) {
				this.$emit( 'input', $event.target.value )
			}
		},
		props: [ 'wpPostId' ]
	}
</script>
