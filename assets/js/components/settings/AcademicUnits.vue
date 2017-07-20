<template>
	<div>
		<div class="add-new-academic-unit">
			<h3>{{ strings.addNewAcademicUnitTitle }}</h3>

			<label
				v-bind:for="'new-academic-unit-name-' + academicUnitTypeSlug"
			>{{ strings.academicUnitNameLabel }}</label>
			<input
				v-bind:id="'new-academic-unit-name-' + academicUnitTypeSlug"
				v-model="newUnitName"
			>

			<fieldset v-if="typeSupportsParent" class="new-academic-unit-parent">
				<legend>{{ strings.parent }}</legend>
			</fieldset>

			<button
				class="button button-primary"
				:disabled="addNewIsLoading || ! newUnitName"
				v-on:click="onAddNewSubmit"
			>{{ strings.addNewAcademicUnitTitle }}</button>
		</div>

		<div class="academic-unit-list">
			{{academicUnitType}}
		</div>
	</div>
</template>

<script>
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			academicUnitType() {
				return this.$store.state.academicUnitTypes[ this.academicUnitTypeSlug ]
			},

			// Used as a key in the store.
			newUnitSlug() {
				return '_new-' + this.academicUnitTypeSlug
			},

			newUnitName: {
				get() {
					return this.$store.state.academicUnits[ this.newUnitSlug ].name
				},
				set( value ) {
					this.$store.state.academicUnits[ this.newUnitSlug ].name = value
				}
			},

			newUnitParent: {
				get() {
					return this.$store.state.academicUnits[ this.newUnitSlug ].parent
				},
				set( value ) {
					this.$store.state.academicUnits[ this.newUnitSlug ].parent = value
				}
			},

			typeSupportsParent() {
				return this.academicUnitType.parent.length > 0
			}
		},

		data() {
			return {
				addNewIsLoading: false
			}
		},

		methods: {
			onAddNewSubmit: function() {
				let unitType = this
				unitType.addNewIsLoading = true
				unitType.$store.dispatch( 'submitEntity', {
					apiRoute: 'academic-unit',
					itemsKey: 'academicUnits',
					slug: unitType.newUnitSlug
				} )
				.then( unitType.checkStatus )
				.then( unitType.parseJSON, unitType.ajaxError )
				.then( function( data ) {
//					itemType.setEntityProp( 'id', data.id )
					/*
					itemType.$store.commit( 'orderEntities', {
						itemsKey: itemType.itemsKey,
						namesKey: itemType.namesKey
					} )
					*/

					unitType.addNewIsLoading = false
				} )
			}
		},

		mixins: [
			i18nTools
		],

		props: {
			academicUnitTypeSlug: {
				type: String,
				required: true
			}
		}
	}
</script>
