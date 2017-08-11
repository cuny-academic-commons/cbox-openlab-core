<template>
	<tr>
		<template v-if="isEditing">
			<th scope="row" class="check-column"></th>

			<td colspan="3" class="academic-unit-edit">
				<h4>{{ strings.edit }}</h4>

				<div class="academic-unit-edit-field-set">
					<div class="academic-unit-edit-field">
						<label :v-for="academicUnit.type + '-' + slug + '-name'">{{ strings.name }}</label>
						<input :v-id="academicUnit.type + '-' + slug + '-name'" v-model="unitName" />
					</div>

					<div class="academic-unit-edit-field">
						<label :v-for="academicUnit.type + '-' + slug + '-order'">{{ strings.orderLegend }}</label>
						<input :v-id="academicUnit.type + '-' + slug + '-order'" v-model="unitOrder" />
					</div>
				</div>

				<div class="academic-unit-edit-field-set">
					<div class="academic-unit-edit-field" v-if="typeSupportsParent">
						<label :v-for="academicUnit.type + '-' + slug + '-parent'">{{ strings.parent }}</label>
						<AcademicUnitParentSelector
							:academicUnitTypeSlug="academicUnit.type"
							:thisUnitSlug="academicUnit.slug"
						/>
					</div>
				</div>

				<button
					class="academic-unit-edit-button academic-unit-edit-cancel button button-secondary"
					v-on:click="onCancelClick"
				>{{ strings.cancel }}</button>

				<button
					class="academic-unit-edit-button academic-unit-edit-save button button-primary"
					v-on:click="onSaveClick"
				>{{ strings.update }}</button>
			</td>
		</template>

		<template v-else>
			<th scope="row" class="check-column">
				<label class="screen-reader-text" :v-for="academicUnit.type + '-' + slug + '-cb'">{{ checkboxLabel }}</label>
				<input type="checkbox" :v-id="academicUnit.type + '-' + slug + '-cb'" :value="academicUnit.id" />
			</th>

			<td class="name column-name has-row-actions column-primary">
				<strong>{{ academicUnit.name }}</strong>
				<br />
				<div class="row-actions">
					<span class="edit"><a href="#" v-on:click="onEditClick">{{ strings.edit }}</a> | </span>
					<span class="delete"><a href="#" class="delete-tag" v-on:click="onDeleteClick">{{ strings.delete }}</a></span>
				</div>
			</td>

			<td class="parent column-parent">
				{{ parentName }}
			</td>

			<td class="posts column-posts">
				{{ academicUnit.count }}
			</td>
		</template>
	</tr>
</template>

<script>
	import AcademicUnitParentSelector from './AcademicUnitParentSelector.vue'
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		components: {
			AcademicUnitParentSelector
		},

		computed: {
			checkboxLabel() {
				return this.strings.selectUnit.replace( '%s', this.academicUnit.name )
			},

			parentName() {
				const parentSlug = this.academicUnit.parent
				let name = ''

				if ( this.$store.state.academicUnits.hasOwnProperty( parentSlug ) ) {
					name = this.$store.state.academicUnits[ parentSlug ].name
				}

				return name
			},

			typeSupportsParent() {
				const typeSlug = this.academicUnit.type
				return this.$store.state.academicUnitTypes[ typeSlug ].parent.length > 0
			}
		},

		data() {
			const academicUnit = this.$store.state.academicUnits[ this.slug ]

			return {
				academicUnit,
				isEditing: false,
				unitName: academicUnit.name,
				unitOrder: academicUnit.order,
				unitParent: academicUnit.parent,
			}
		},

		methods: {
			onDeleteClick( event ) {
				event.preventDefault()

				if ( ! confirm( this.strings.deleteConfirm ) ) {
					return
				}

				let unit = this
				const unitId = unit.academicUnit.id
				unit.isLoading = true
				if ( unitId ) {
					unit.$store.dispatch( 'submitDeleteEntity', {
						apiRoute: 'academic-unit',
						id: unitId
					} )
					.then( unit.checkStatus )
					.then( unit.parseJSON, unit.ajaxError )
					.then( function( data ) {
						unit.$store.commit( 'removeEntity', {
							itemsKey: 'academicUnits',
							namesKey: 'academicUnitNames',
							slug: unit.slug
						} )
					} )
				}
			},

			onCancelClick( event ) {
				event.preventDefault()
				this.isEditing = ! this.isEditing
				this.unitName = this.academicUnit.name
				this.unitOrder = this.academicUnit.order
				this.unitParent = this.academicUnit.parent
			},

			onEditClick( event ) {
				event.preventDefault()
				this.isEditing = ! this.isEditing
			},

			onSaveClick( event ) {
				event.preventDefault()
				let unit = this
				unit.addNewIsLoading = true

				// Save temp values to application store.
				unit.$store.commit( 'setEntityProperty', {
					itemsKey: 'academicUnits',
					property: 'name',
					slug: unit.academicUnit.slug,
					value: this.unitName
				} )

				/*
				unit.$store.commit( 'setEntityProperty', {
					itemsKey: 'academicUnits',
					property: 'parent',
					slug: unit.academicUnit.slug,
					value: this.unitParent
				} )
				*/

				unit.$store.commit( 'setEntityProperty', {
					itemsKey: 'academicUnits',
					property: 'order',
					slug: unit.academicUnit.slug,
					value: this.unitOrder
				} )

				unit.$store.dispatch( 'submitEntity', {
					apiRoute: 'academic-unit',
					itemsKey: 'academicUnits',
					slug: this.academicUnit.slug
				} )
				.then( unit.checkStatus )
				.then( unit.parseJSON, unit.ajaxError )
				.then( function( response ) {
					return response.json()
				} )
				.then( function( data ) {
					unit.addNewIsLoading = false
					unit.isEditing = false
					unit.academicUnit = data

					unit.$store.commit( 'orderEntities', {
						itemsKey: 'academicUnits',
						namesKey: 'academicUnitNames',
					} )
				} )
			}
		},

		mixins: [
			i18nTools
		],

		props: {
			slug: {
				type: String,
				required: true
			}
		}
	}
</script>
