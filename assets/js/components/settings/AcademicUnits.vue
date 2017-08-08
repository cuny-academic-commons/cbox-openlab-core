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

				<AcademicUnitParentSelector
					:academicUnitTypeSlug="academicUnitTypeSlug"
					:thisUnitSlug="newUnitSlug"
				/>
			</fieldset>

			<button
				class="button button-primary"
				:disabled="addNewIsLoading || ! newUnitName"
				v-on:click="onAddNewSubmit"
			>{{ strings.addNewAcademicUnitTitle }}</button>
		</div>

		<div class="academic-unit-list">
			<table class="wp-list-table widefat fixed striped">
				<thead><tr>
					<td :id="academicUnitTypeSlug + '-cb'" class="manage-column column-cb check-column">
						<label class="screen-reader-text" :for="academicUnitTypeSlug + '-cb-select-all'">{{ strings.selectAll }}</label>
						<input :id="academicUnitTypeSlug + '-cb-select-all'" type="checkbox" />
					</td>

					<td :id="academicUnitTypeSlug + '-name'" class="manage-column column-name column-primary" scope="col">
						{{ strings.name }}
					</td>

					<td :id="academicUnitTypeSlug + '-parent'" class="manage-column column-parent" scope="col">
						{{ strings.parent }}
					</td>

					<td :id="academicUnitTypeSlug + '-posts'" class="manage-posts column-posts" scope="col">
						{{ strings.count }}
					</td>
				</tr></thead>

				<tbody>
					<template v-if="unitsOfType.length > 0" v-for="unitSlug in unitsOfType">
						<AcademicUnit :slug="unitSlug" />
					</template>

					<tr v-if="unitsOfType.length === 0">
						<td colspan="4">{{ strings.noUnitsOfType }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
	import AcademicUnit from './AcademicUnit.vue'
	import AcademicUnitParentSelector from './AcademicUnitParentSelector.vue'

	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		components: {
			AcademicUnit,
			AcademicUnitParentSelector
		},

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
					this.$store.commit( 'setEntityProperty', {
						itemsKey: 'academicUnits',
						property: 'name',
						slug: this.newUnitSlug,
						value
					} );
				}
			},

			typeSupportsParent() {
				return this.academicUnitType.parent.length > 0
			},

			unitsOfType() {
				let units = []
				for ( let unitSlug in this.$store.state.academicUnits ) {
					if ( 0 == this.$store.state.academicUnits[ unitSlug ].id ) {
						continue
					}

					if ( this.academicUnitTypeSlug !== this.$store.state.academicUnits[ unitSlug ].type ) {
						continue
					}

					units.push( unitSlug )
				}

				return units
			}
		},

		data() {
			return {
				addNewIsLoading: false
			}
		},

		methods: {
			onAddNewSubmit: function() {
				let unit = this
				unit.addNewIsLoading = true
				unit.$store.dispatch( 'submitEntity', {
					apiRoute: 'academic-unit',
					itemsKey: 'academicUnits',
					slug: unit.newUnitSlug
				} )
				.then( unit.checkStatus )
				.then( unit.parseJSON, unit.ajaxError )
				.then( function( data ) {
					unit.$store.commit( 'setEntityProperty', {
						itemsKey: 'academicUnits',
						property: 'id',
						slug: unit.newUnitSlug,
						value: data.id
					} )
					unit.addNewIsLoading = false
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
