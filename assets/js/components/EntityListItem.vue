<template>
	<div v-bind:class="itemClass">
		<div class="cboxol-entity-header">
			<div v-if="isSortable" class="sortable-handle"><span class="screen-reader-text">{{ strings.dragToSort }}</span></div>

			<div class="cboxol-entity-header-label">
				{{ name }} <span class="entity-off" v-if="! isEnabled">{{ strings.off }}</span>
			</div>

			<div class="cboxol-entity-header-actions">
				<a href="" v-on:click="onDeleteClick" v-if="canBeDeleted && typeSupportsDeletion">
					<span>{{ strings.delete }}</span>
				</a>
				<span v-if="canBeDeleted && typeSupportsDeletion"> | </span>
				<a class="cboxol-entity-edit" href="" v-on:click="onAccordionClick">
					<span v-if="isCollapsed">{{ strings.edit }}</span>
					<span v-else>{{ strings.editing }}</span>
				</a>
				<a class="cboxol-entity-edit-arrow" href="" v-on:click="onAccordionClick"><span class="screen-reader-text">{{ strings.edit }}</span></a>
			</div>
		</div>

		<div class="cboxol-entity-content">
			<div v-if="isToggleable" class="cboxol-entity-content-section">
				<on-off-switch
					:entityType="entityType"
					:slug="slug"
				/>
			</div>

			<div class="cboxol-entity-content-section">
				<label
					v-bind:for="slug + '-name'"
					class="cboxol-entity-content-section-header"
				>{{ strings.itemTypeNameLabel }}</label>
				<input
					v-bind:placeholder="addNewPlaceholder"
					v-bind:id="slug + '-name'"
					v-model="name"
					v-bind:autofocus="! name"
				>

				<div v-if="showGroupTypeDesignedMessage" class="cboxol-entity-designed-for-gloss">
					{{ entityData.isCourse ? strings.thisGroupTypeIsDesignedForCourses : strings.thisGroupTypeIsDesignedForPortfolios }}
				</div>
			</div>

			<div v-if="supportsParent" class="cboxol-entity-content-section">
				<label
					v-bind:for="slug + '-parent'"
					class="cboxol-entity-content-section-header"
				>{{ strings.parent }}</label>
				<select
					v-bind:id="slug + '-parent'"
					v-model="parent"
				>
					<option value="0">{{ strings.none }}</option>
					<option v-for="sibling in getSiblings" :value="sibling.slug">{{ sibling.name }}</option>
				</select>
			</div>

			<div v-if="supportsAssociatedWithMemberTypes" class="cboxol-entity-content-section">
				<h3 class="cboxol-entity-content-section-header">{{ strings.associatedWithMemberTypes }}</h3>

				<AssociatedTypeDropdowns
					associatedType="memberTypes"
					:entityType="entityType"
					:slug="slug"
				/>
			</div>

			<div v-if="supportsAssociatedWithGroupTypes" class="cboxol-entity-content-section">
				<h3 class="cboxol-entity-content-section-header">{{ strings.associatedWithGroupTypes }}</h3>

				<AssociatedTypeDropdowns
					associatedType="groupTypes"
					:entityType="entityType"
					:slug="slug"
				/>
			</div>

			<!-- durrrrr -->
			<div v-if="'groupCategory' === entityType" class="cboxol-entity-content-section associated-group-types">
				<fieldset>
					<h3 class="cboxol-entity-content-section-header"><legend>{{ strings.associatedWithGroupTypes }}</legend></h3>
					<AssociatedGroupTypeCheckboxes
						:entityType="entityType"
						:slug="slug"
					/>
				</fieldset>
			</div>

			<div class="cboxol-entity-content-section item-type-settings" v-if="showSettings">
				<h3 class="cboxol-entity-content-section-header">{{ strings.settings }}</h3>

				<div v-for="setting in entityData.settings">
					<component
						:entityType="entityType"
						:is="setting.component"
						v-bind:slug="slug"
					/>
				</div>
			</div>

			<div class="cboxol-entity-content-section item-type-labels" v-if="showLabels">
				<h3 class="cboxol-entity-content-section-header">{{ strings.labels }}</h3>

				<div v-for="label in entityData.labels">
					<type-label
						:entityType="entityType"
						v-bind:typeSlug="slug"
						v-bind:labelSlug="label.slug"
					></type-label>
				</div>
			</div>

			<!-- durrrrr -->
			<div v-if="'group' === objectType && 'groupType' === entityType" class="cboxol-entity-content-section item-type-template">
				<h3 class="cboxol-entity-content-section-header">{{ strings.template }}</h3>

				<p>{{ strings.templateSiteDescription }}</p>

				<div class="cboxol-template-site-links">
					<a v-bind:href="templateAdminUrl">{{ strings.templateDashboardLink }}</a> | <a v-bind:href="templateUrl">{{ strings.templateViewLink }}</a>
				</div>

			</div>

			<div class="cboxol-entity-submit">
				<button class="button button-primary" v-on:click="onSubmit" v-bind:disabled="isLoading || ! isModified">{{ saveButtonText }}</button>
			</div>

			<div v-if="supportsAcademicUnits" class="cboxol-academic-units-of-type">
				<AcademicUnits
					:academicUnitTypeSlug="slug"
				/>
			</div>
		</div>
	</div>
</template>

<script>
	import AjaxTools from '../mixins/AjaxTools.js'
	import EntityTools from '../mixins/EntityTools.js'
	import i18nTools from '../mixins/i18nTools.js'

	import OnOffSwitch from './OnOffSwitch.vue'
	import TypeLabel from './TypeLabel.vue'

	// All settings components must be available.
	import AcademicUnits from './settings/AcademicUnits.vue'
	import AssociatedGroupTypeCheckboxes from './settings/AssociatedGroupTypeCheckboxes.vue'
	import AssociatedTypeDropdowns from './settings/AssociatedTypeDropdowns.vue'
	import MayCreateCourses from './settings/MayCreateCourses.vue'
	import MayChangeMemberTypeTo from './settings/MayChangeMemberTypeTo.vue'
	import Order from './settings/Order.vue'

	export default {
		components: {
			AcademicUnits,
			AssociatedTypeDropdowns,
			AssociatedGroupTypeCheckboxes,
			OnOffSwitch,
			MayCreateCourses,
			MayChangeMemberTypeTo,
			Order,
			TypeLabel
		},

		computed: {
			addNewPlaceholder() {
				return this.getEntityTypeProp( 'addNewPlaceholder' )
			},

			entityData() {
				return this.$store.state[ this.itemsKey ][ this.slug ]
			},

			getSiblings() {
				let siblings = {}
				let sibling
				for ( let s in this.$store.state[ this.itemsKey ] ) {
					sibling = this.$store.state[ this.itemsKey ][ s ]
					if ( sibling.slug !== this.slug ) {
						siblings[ s ] = sibling
					}
				}

				return siblings
			},

			showLabels() {
				let hasLabels = false, s = null
				if ( this.entityData.hasOwnProperty( 'labels' ) ) {
					for ( s in this.entityData.labels ) {
						hasLabels = true
						break
					}
				}

				return hasLabels
			},

			showSettings() {
				let hasSettings = false, s = null
				if ( this.entityData.hasOwnProperty( 'settings' ) ) {
					for ( s in this.entityData.settings ) {
						hasSettings = true
						break
					}
				}

				return hasSettings
			},

			itemClass() {
				let itemClass = 'cboxol-entity'

				itemClass += ' cboxol-entity-' + this.entityType

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

			objectType: function() {
				return this.$store.state.objectType;
			},

			saveButtonText() {
				if ( this.isLoading ) {
					return this.strings.saving
				} else if ( this.isModified ) {
					return this.strings.saveChanges
				} else {
					return this.strings.saved
				}
			},

			showGroupTypeDesignedMessage() {
				return ( this.entityData.hasOwnProperty('isCourse') && this.entityData.isCourse ) || ( this.entityData.hasOwnProperty('isPortfolio') && this.entityData.isPortfolio )
			},

			supportsAssociatedWithMemberTypes() {
				return this.itemsKey === 'academicUnitTypes'
			},

			supportsAssociatedWithGroupTypes() {
				return this.itemsKey === 'academicUnitTypes'
			},

			supportsAcademicUnits() {
				return this.itemsKey === 'academicUnitTypes'
			},

			supportsParent() {
				return this.entityData.hasOwnProperty( 'parent' )
			},

			templateUrl() {
				const templateSite = this.getEntityProp( 'templateSite' )
				return templateSite.url
			},

			templateAdminUrl() {
				const templateSite = this.getEntityProp( 'templateSite' )
				return templateSite.adminUrl
			},

			typeSupportsDeletion() {
				return 'groupType' !== this.entityType
			}
		},

		methods: {
			onAccordionClick: function( event ) {
				event.preventDefault()
				this.$store.commit( 'toggleCollapsed', {
					itemsKey: this.itemsKey,
					slug: this.slug
				} )
			},

			getElId: function( base ) {
				return this.slug + '-' . base
			},

			onDeleteClick: function( event ) {
				event.preventDefault()

				if ( ! confirm( this.strings.deleteConfirm ) ) {
					return
				}

				let itemType = this
				itemType.isLoading = true
				if ( itemType.id > 0 ) {
					itemType.$store.dispatch( 'submitDeleteEntity', {
						apiRoute: itemType.apiRoute,
						id: itemType.id
					} )
					.then( itemType.checkStatus )
					.then( itemType.parseJSON, itemType.ajaxError )
					.then( function( data ) {
						itemType.$store.commit( 'removeEntity', {
							itemsKey: itemType.itemsKey,
							namesKey: itemType.namesKey,
							slug: itemType.slug
						} )

						itemType.$store.commit( 'orderEntities', {
							itemsKey: itemType.itemsKey,
							namesKey: itemType.namesKey
						} )

						if ( itemType.isSortable ) {
							itemType.updateEntityOrder()
						}
					} )
				}
			},

			onSubmit: function() {
				let itemType = this
				itemType.isLoading = true

				// Any empty labels should be set to the value of 'name'.
				let itemLabel
				if ( 'undefined' !== itemType.entityData.labels ) {
					for ( let i in itemType.entityData.labels ) {
						itemLabel = itemType.entityData.labels[ i ]
						if ( '' === itemLabel.value ) {
							itemType.$store.commit( 'setLabel', {
								itemsKey: this.itemsKey,
								labelSlug: itemLabel.slug,
								typeSlug: this.slug,
								value: this.name
							} )
						}
					}
				}

				itemType.$store.dispatch( 'submitEntity', {
					apiRoute: itemType.apiRoute,
					itemsKey: itemType.itemsKey,
					slug: itemType.slug
				} )
					.then( itemType.checkStatus )
					.then( itemType.parseJSON, itemType.ajaxError )
					.then( function( data ) {
						if ( '_new' === itemType.slug.substr( 0, 4 ) && 'academicUnitType' === itemType.entityType ) {
							window.onbeforeunload = null
							window.location.reload()
							return
						}

						itemType.isModified = false

						// If this is a new item, add it to the end of the entity list.
						if ( '_new' === itemType.slug.substr( 0, 4 ) ) {
							itemType.$store.commit( 'addEntity', {
								item: data,
								key: data,
								itemsKey: itemType.itemsKey,
								namesKey: itemType.namesKey
							} )
						}

						itemType.setEntityProp( 'id', data.id )
						itemType.$store.commit( 'orderEntities', {
							itemsKey: itemType.itemsKey,
							namesKey: itemType.namesKey
						} )

						if ( itemType.isSortable ) {
							itemType.updateEntityOrder()
						}

						// Reset the dummy.
						if ( '_new' === itemType.slug.substr( 0, 4 ) ) {
							itemType.$store.commit( 'removeEntity', {
								itemsKey: itemType.itemsKey,
								namesKey: itemType.namesKey,
								slug: itemType.slug
							} )
						}
					} )
			},
		},

		mixins: [
			AjaxTools,
			EntityTools,
			i18nTools
		],

		props: [
			'entityType',
			'isSortable',
			'isToggleable',
			'slug'
		],
	}
</script>
