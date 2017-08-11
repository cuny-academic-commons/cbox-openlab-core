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

			<p class="add-new-academic-unit-field-description">Sint omnis debitis aut libero accusantium cumque fugit quo. Eveniet dolore ut optio autem tempora.</p>

			<fieldset v-if="typeSupportsParent" class="new-academic-unit-parent">
				<legend>{{ strings.parent }}</legend>

				<AcademicUnitParentSelector
					:academicUnitTypeSlug="academicUnitTypeSlug"
					:thisUnitSlug="newUnitSlug"
				/>
			</fieldset>

			<p class="add-new-academic-unit-field-description">Sint omnis debitis aut libero accusantium cumque fugit quo. Eveniet dolore ut optio autem tempora.</p>

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
				let currentUnit
				for ( let i in this.$store.state.academicUnitNames ) {
					currentUnit = this.$store.state.academicUnits[ this.$store.state.academicUnitNames[ i ] ]

					if ( 0 == currentUnit.id ) {
						continue
					}

					if ( this.academicUnitTypeSlug !== currentUnit.type ) {
						continue
					}

					units.push( currentUnit.slug )
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
				.then( function( response ) {
					return response.json()
				} )
				.then( function( data ) {
					unit.$store.commit( 'addEntity', {
						item: data,
						key: data.slug,
						itemsKey: 'academicUnits',
						namesKey: 'academicUnitNames',
					} )

					unit.$store.commit( 'setEntityProperty', {
						itemsKey: 'academicUnits',
						property: 'parent',
						slug: unit.newUnitSlug,
						value: ''
					} );

					/*
					unit.$store.commit( 'removeEntity', {
						slug: unit.newUnitSlug,
						itemsKey: 'academicUnits',
						namesKey: 'academicUnitNames',
					} )
					*/

					/*
					unit.$store.commit( 'orderEntities', {
						itemsKey: 'academicUnits',
						namesKey: 'academicUnitNames',
					} )
					*/
					unit.addNewIsLoading = false
					unit.newUnitName = ''
//					unit.newUnitParent = ''
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
