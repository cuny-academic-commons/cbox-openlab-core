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

			<div class="new-academic-unit-parent">
				<label :for="academicUnitTypeSlug + '-new-parent'">{{ strings.parent }}</label>
				<AcademicUnitParentSelector
					:academicUnitTypeSlug="academicUnitType.parent"
					:fieldId="academicUnitTypeSlug + '-new-parent'"
					:thisUnitSlug="newUnitSlug"
				/>

				<p class="add-new-academic-unit-field-description">{{ strings.academicUnitParentLegend }}</p>
			</div>

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
						<AcademicUnit
							:academicUnitTypeSlug="academicUnitTypeSlug"
							:slug="unitSlug"
						/>
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
					if ( this.$store.state.academicUnits.hasOwnProperty( this.newUnitSlug ) ) {
						return this.$store.state.academicUnits[ this.newUnitSlug ].name
					} else {
						return ''
					}
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
				const parent = this.academicUnitType.parent
				return undefined !== parent && parent.length > 0
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

					// Reset form data.
					unit.newUnitName = ''
					unit.$store.commit( 'setEntityProperty', {
						itemsKey: 'academicUnits',
						property: 'parent',
						slug: unit.newUnitSlug,
						value: ''
					} );

					unit.$store.commit( 'orderEntities', {
						itemsKey: 'academicUnits',
						namesKey: 'academicUnitNames',
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
