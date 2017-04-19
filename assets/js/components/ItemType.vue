<template>
	<div v-bind:class="getItemClass()">
		<div class="cboxol-item-type-header">
			<div class="cboxol-item-type-header-label">
				{{ data.name }}
			</div>

			<div class="cboxol-item-type-header-actions">
				<span v-on:click="onAccordionClick">
					<span v-if="isCollapsed">Edit &#x25BC;</span>
					<span v-else>Editing &#x25B2;</span>
				</span>
			</div>
		</div>

		<div class="cboxol-item-type-content">
			<on-off-switch v-bind:slug="data.slug"></on-off-switch>

			<label
				v-bind:for="data.slug + '-name'"
			>{{ strings.itemTypeNameLabel }}</label>
			<input
				v-bind:placeholder="strings.addNewType"
				v-bind:id="data.slug + '-name'"
				v-model="data.name"
			>

			<div class="cboxol-item-type-content-section item-type-settings">
				<h3>{{ strings.settings }}</h3>

				<div v-for="setting in data.settings">
					<component :is="setting.component" v-bind:slug="data.slug"></component>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import OnOffSwitch from './OnOffSwitch.vue'

	// All settings components must be available.
	import MayCreateCourses from './settings/MayCreateCourses.vue'
	import MayChangeMemberTypeTo from './settings/MayChangeMemberTypeTo.vue'

	export default {
		data() {
			return {
				strings: CBOXOLStrings.strings,
				data: this.$store.state.types[ this.slug ]
			}
		},

		props: ['slug'],

		components: {
			OnOffSwitch,
			MayCreateCourses,
			MayChangeMemberTypeTo
		},

		computed: {
			isCollapsed() {
				return this.data.isCollapsed
			},
			isEnabled() {
				return this.data.isEnabled
			}
		},

		methods: {
			onAccordionClick: function( event ) {
				this.$store.commit( 'toggleCollapsed', { slug: this.slug } )
			},

			getElId: function( base ) {
				return this.slug + '-' . base
			},

			getItemClass: function() {
				let itemClass = 'cboxol-item-type'

				if ( this.isCollapsed ) {
					itemClass += ' collapsed'
				}

				return itemClass
			}

		}
	}
</script>
