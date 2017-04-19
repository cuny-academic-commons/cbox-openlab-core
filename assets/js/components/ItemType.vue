<template>
	<div v-bind:class="itemClass">
		<div class="cboxol-item-type-header">
			<div class="cboxol-item-type-header-label">
				{{ name }} <span class="item-type-off" v-if="! isEnabled">{{ strings.off }}</span>
			</div>

			<div class="cboxol-item-type-header-actions">
				<a href="" v-on:click="onAccordionClick">
					<span v-if="isCollapsed">{{ strings.edit }}</span>
					<span v-else>{{ strings.editing }}</span>
				</a>
			</div>
		</div>

		<div class="cboxol-item-type-content">
			<div class="cboxol-item-type-content-section">
				<on-off-switch v-bind:slug="data.slug"></on-off-switch>
			</div>

			<div class="cboxol-item-type-content-section">
				<label
					v-bind:for="data.slug + '-name'"
					class="cboxol-item-type-content-section-header"
				>{{ strings.itemTypeNameLabel }}</label>
				<input
					v-bind:placeholder="strings.addNewType"
					v-bind:id="data.slug + '-name'"
					v-model="name"
					v-on:change="setIsModified"
				>
			</div>

			<div class="cboxol-item-type-content-section item-type-settings">
				<h3 class="cboxol-item-type-content-section-header">{{ strings.settings }}</h3>

				<div v-for="setting in data.settings">
					<component :is="setting.component" v-bind:slug="data.slug"></component>
				</div>
			</div>

			<div class="cboxol-item-type-content-section item-type-labels">
				<h3 class="cboxol-item-type-content-section-header">{{ strings.labels }}</h3>

				<div v-for="label in data.labels">
					<type-label v-bind:typeSlug="data.slug" v-bind:labelSlug="label.slug"></type-label>
				</div>
			</div>

			<div class="cboxol-item-type-submit">
				<button class="button button-primary" v-on:click="onSubmit" v-bind:disabled="isLoading || ! isModified">{{ saveButtonText }}</button>
			</div>
		</div>
	</div>
</template>

<script>
	import OnOffSwitch from './OnOffSwitch.vue'
	import TypeLabel from './TypeLabel.vue'

	// All settings components must be available.
	import MayCreateCourses from './settings/MayCreateCourses.vue'
	import MayChangeMemberTypeTo from './settings/MayChangeMemberTypeTo.vue'
	import Order from './settings/Order.vue'

	export default {
		data() {
			return {
				strings: CBOXOLStrings.strings,
				data: this.$store.state.types[ this.slug ],
			}
		},

		props: ['slug'],

		components: {
			OnOffSwitch,
			MayCreateCourses,
			MayChangeMemberTypeTo,
			Order,
			TypeLabel
		},

		computed: {
			isCollapsed() {
				return this.data.isCollapsed
			},
			isEnabled: {
				get() {
					return this.$store.state.types[ this.slug ].isEnabled
				}
			},
			itemClass() {
				let itemClass = 'cboxol-item-type'

				if ( this.isCollapsed ) {
					itemClass += ' collapsed'
				}

				if ( this.isLoading ) {
					itemClass += ' loading'
				}

				if ( ! this.isEnabled ) {
					itemClass += ' disabled'
				}

				return itemClass
			},

			isLoading: {
				get() {
					return this.$store.state.types[ this.slug ].isLoading
				},
				set( value ) {
					this.$store.commit( 'setTypeProperty', { slug: this.slug, property: 'isLoading', value: value } )
				}
			},

			isModified: {
				get() {
					return this.$store.state.types[ this.slug ].isModified
				},
				set( value ) {
					this.$store.commit( 'setTypeProperty', { slug: this.slug, property: 'isModified', value: value } )
				}
			},

			name: {
				get() {
					return this.$store.state.types[ this.slug ].name
				},
				set( value ) {
					this.setIsModified()
					this.$store.commit( 'setTypeProperty', { slug: this.slug, property: 'name', value: value } )
				}
			},

			saveButtonText() {
				if ( this.isLoading ) {
					return this.strings.saving
				} else if ( this.isModified ) {
					return this.strings.saveChanges
				} else {
					return this.strings.saved
				}
			}
		},

		methods: {
			onAccordionClick: function( event ) {
				event.preventDefault()
				this.$store.commit( 'toggleCollapsed', { slug: this.slug } )
			},

			getElId: function( base ) {
				return this.slug + '-' . base
			},

			onSubmit: function() {
				this.isLoading = true
				this.$store.dispatch( 'submitForm', { slug: this.slug } ).then( response => {
					if ( response.status >= 200 && response.status < 300 ) {
						this.isModified = false
					} else {

					}

					this.isLoading = false
				} )
			},

			setIsModified() {
				this.$store.commit( 'setTypeProperty', { slug: this.slug, property: 'isModified', value: true } )
			}
		}
	}
</script>
