<template>
	<div v-bind:class="itemClass">
		<div class="cboxol-entity-header">
			<div class="cboxol-entity-header-label">
				{{ name }} <span class="entity-off" v-if="! isEnabled">{{ strings.off }}</span>
			</div>

			<div class="cboxol-entity-header-actions">
				<a href="" v-on:click="onDeleteClick" v-if="canBeDeleted">
					<span>{{ strings.delete }}</span>
				</a>
				<span v-if="canBeDeleted"> | </span>
				<a class="cboxol-entity-edit" href="" v-on:click="onAccordionClick">
					<span v-if="isCollapsed">{{ strings.edit }}</span>
					<span v-else>{{ strings.editing }}</span>
				</a>
				<a class="cboxol-entity-edit-arrow" href="" v-on:click="onAccordionClick"></a>
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
					<type-label v-bind:typeSlug="slug" v-bind:labelSlug="label.slug"></type-label>
				</div>
			</div>

			<!-- durrrrr -->
			<div v-if="'group' === objectType && 'itemType' === entityType" class="cboxol-entity-content-section item-type-template">
				<h3 class="cboxol-entity-content-section-header">{{ strings.template }}</h3>

				<p>{{ strings.templateSiteDescription }}</p>

				<div class="cboxol-template-site-links">
					<a v-bind:href="templateAdminUrl">{{ strings.templateDashboardLink }}</a> | <a v-bind:href="templateUrl">{{ strings.templateViewLink }}</a>
				</div>

			</div>

			<div class="cboxol-entity-submit">
				<button class="button button-primary" v-on:click="onSubmit" v-bind:disabled="isLoading || ! isModified">{{ saveButtonText }}</button>
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
	import MayCreateCourses from './settings/MayCreateCourses.vue'
	import MayChangeMemberTypeTo from './settings/MayChangeMemberTypeTo.vue'
	import Order from './settings/Order.vue'

	export default {
		components: {
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

			templateUrl() {
				const templateSite = this.getEntityProp( 'templateSite' )
				return templateSite.url
			},

			templateAdminUrl() {
				const templateSite = this.getEntityProp( 'templateSite' )
				return templateSite.adminUrl
			},
		},

		methods: {
			onAccordionClick: function( event ) {
				event.preventDefault()
				this.$store.commit( 'toggleCollapsed', { slug: this.slug } )
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
						itemType.$store.commit( 'removeType', { slug: itemType.slug } )
						itemType.$store.commit( 'orderEntities', {
							itemsKey: itemType.itemsKey,
							namesKey: itemType.namesKey
						} )
					} )
				}
			},

			onSubmit: function() {
				let itemType = this
				itemType.isLoading = true
				itemType.$store.dispatch( 'submitEntity', {
					apiRoute: itemType.apiRoute,
					itemsKey: itemType.itemsKey,
					slug: itemType.slug
				} )
					.then( itemType.checkStatus )
					.then( itemType.parseJSON, itemType.ajaxError )
					.then( function( data ) {
						itemType.isModified = false

						itemType.setEntityProp( 'id', data.id )
						itemType.$store.commit( 'orderEntities', {
							itemsKey: itemType.itemsKey,
							namesKey: itemType.namesKey
						} )

						itemType.isLoading = false
						itemType.isCollapsed = true
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
			'isToggleable',
			'slug'
		],

	}
</script>
