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

			<!-- durrrrr -->
			<div v-if="'group' === objectType && 'groupType' === entityType" class="cboxol-entity-content-section item-type-template">
				<h3 class="cboxol-entity-content-section-header">{{ strings.template }}</h3>

				<p>{{ strings.templateSiteDescription }} <a v-bind:href="siteTemplatesAdminUrl">{{ strings.templateSiteAdminDescription }}</a></p>

				<div class="site-templates-list">
					<div class="site-templates-list-item site-templates-list-header">
						<div class="site-template-radio">
							&nbsp;
						</div>

						<div class="site-template-name">
							{{ strings.templates }}
						</div>

						<div class="site-template-links">
							{{ strings.links }}
						</div>

					</div>

					<div v-for="siteTemplate in entityData.siteTemplates" class="site-templates-list-item">
						<div class="site-template-radio">

							<input type="radio" v-model="siteTemplateId" v-bind:value="siteTemplate.id" v-bind:id="'site-template-' + siteTemplate.id" />
						</div>

						<div class="site-template-name">
							<label v-bind:for="'site-template-' + siteTemplate.id">
								{{ siteTemplate.name }}
							</label>
						</div>

						<div class="site-template-links">
							<a v-bind:href="siteTemplate.adminUrl">{{ strings.templateDashboardLink }}</a> | <a v-bind:href="siteTemplate.url">{{ strings.templateViewLink }}</a>
						</div>

					</div>
				</div>
			</div>

			<div v-if="'group' === objectType && 'groupType' === entityType" class="cboxol-entity-content-section item-type-template">
				<h3 class="cboxol-entity-content-section-header">{{ strings.defaultPrivacySettings }}</h3>

				<p>{{ strings.defaultPrivacySettingsDescription }}</p>

				<h4 class="cboxol-entity-content-section-subheader">{{ strings.groupHomeAvailableOptionsHeading }}</h4>

				<p>{{ strings.groupHomeAvailableOptionsDescription }}</p>

				<fieldset>
					<legend class="screen-reader-text">{{ strings.groupHomeAvailableOptions }}</legend>

					<div class="cboxol-group-type-privacy-level">
						<label>
							<input
								type="checkbox"
								value="public"
								:checked="selectedPrivacyOptions.includes('public')"
								@change="togglePrivacy('public')"
							>
							{{ strings.public }}
						</label>

						<ul>
							<li>{{ strings.groupContentIsPublic }}</li>
							<li>{{ strings.groupDirectoryIsPublic }}</li>
							<li>{{ strings.groupJoiningIsPublic }} </li>
						</ul>
					</div>

					<div class="cboxol-group-type-privacy-level">
						<label>
							<input
								type="checkbox"
								value="private"
								:checked="selectedPrivacyOptions.includes('private')"
								@change="togglePrivacy('private')"
							>
							{{ strings.private }}
						</label>

						<ul>
							<li>{{ strings.groupContentIsPrivate }}</li>
							<li>{{ strings.groupDirectoryIsPublic }}</li>
							<li>{{ strings.groupJoiningIsPrivate }}</li>
						</ul>
					</div>

					<div class="cboxol-group-type-privacy-level">
						<label>
							<input
								type="checkbox"
								value="hidden"
								:checked="selectedPrivacyOptions.includes('hidden')"
								@change="togglePrivacy('hidden')"
							>
							{{ strings.hidden }}
						</label>

						<ul>
							<li>{{ strings.groupContentIsPrivate }}</li>
							<li>{{ strings.groupDirectoryIsPrivate }}</li>
							<li>{{ strings.groupJoiningInviteOnly }}</li>
						</ul>
					</div>
				</fieldset>

				<h4 class="cboxol-entity-content-section-subheader">{{ strings.groupHomeDefaultOptionHeading }}</h4>

				<fieldset>
					<legend>{{ strings.groupHomeDefaultOptionDescription }}</legend>
						<div
							v-for="option in selectedPrivacyOptions"
							:key="option"
							class="cboxol-group-type-default-radio"
						>
							<label>
								<input
									type="radio"
									:value="option"
									v-model="defaultPrivacy"
									:name="`group-default-privacy-${slug}`"
									:checked="defaultPrivacy === option"
								>
								{{ privacyLabel(option) }}
							</label>
						</div>
				</fieldset>

				<h4 class="cboxol-entity-content-section-subheader">{{ strings.groupSiteAvailableOptionsHeading }}</h4>

				<p>{{ strings.groupSiteAvailableOptionsDescription }}</p>

				<div
					v-for="[ value, text ] in allSiteBlogPublicOptions"
					:key="value"
					class="cboxol-group-type-site-privacy-level"
				>
					<label>
						<input
							type="checkbox"
							:value="value"
							v-model="selectedSitePrivacyOptions"
							:name="`group-selected-site-privacy-options-${slug}`"
						> <span>{{ text }}</span>
					</label>
				</div>

				<h4 class="cboxol-entity-content-section-subheader">{{ strings.groupSiteDefaultOptionHeading }}</h4>

				<fieldset>
					<legend>{{ strings.groupSiteDefaultOptionDescription }}</legend>
						<div
							v-for="option in selectedSitePrivacyOptions"
							:key="option"
							class="cboxol-group-type-default-radio"
						>
							<label>
								<input
									type="radio"
									:value="option"
									v-model="defaultSitePrivacy"
									:name="`group-site-default-privacy-${slug}`"
									:checked="defaultSitePrivacy === option"
								>
								{{ sitePrivacyLabel( option ) }}
							</label>
						</div>
				</fieldset>
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
	import MayImportGroupUsers from './settings/MayImportGroupUsers.vue'
	import Order from './settings/Order.vue'

	export default {
		components: {
			AcademicUnits,
			AssociatedTypeDropdowns,
			AssociatedGroupTypeCheckboxes,
			OnOffSwitch,
			MayCreateCourses,
			MayChangeMemberTypeTo,
			MayImportGroupUsers,
			Order,
			TypeLabel
		},

		computed: {
			addNewPlaceholder() {
				return this.getEntityTypeProp( 'addNewPlaceholder' )
			},

			allSiteBlogPublicOptions() {
				return new Map([
					[  '1', this.strings.groupSiteBlogPublic1 ],
					[  '0', this.strings.groupSiteBlogPublic0 ],
					[ '-1', this.strings.groupSiteBlogPublicNegative1 ],
					[ '-2', this.strings.groupSiteBlogPublicNegative2 ],
					[ '-3', this.strings.groupSiteBlogPublicNegative3 ]
				]);
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

			siteTemplateId: {
				get() {
					return this.entityData.siteTemplateId
				},
				set( value ) {
					this.isModified = true
					this.setEntityProp( 'siteTemplateId', value )
				}
			},

			selectedPrivacyOptions() {
				const rawOptions = this.entityData.availablePrivacyOptions || [];
				const optionOrder = [ 'public', 'private', 'hidden' ];

				// Sort the options based on the order defined in optionOrder
				const sortedOptions = optionOrder.reduce((acc, option) => {
					if (rawOptions.includes(option)) {
						acc.push(option);
					}
					return acc;
				}, []);

				return sortedOptions;
			},

			selectedSitePrivacyOptions: {
				get() {
					const rawOptions = this.entityData.availableSitePrivacyOptions || [];
					// These are numeric strings and should be ordered in descending order.
					const optionOrder = [ '1', '0', '-1', '-2', '-3' ];

					// Sort the options based on the order defined in optionOrder
					const sortedOptions = optionOrder.reduce((acc, option) => {
						if (rawOptions.includes(option.toString())) {
							acc.push(option);
						}
						return acc;
					}, []);

					return sortedOptions;
				},
				set( value ) {
					this.isModified = true
					this.setEntityProp( 'availableSitePrivacyOptions', value )
				}
			},

			defaultPrivacy: {
				get() {
					return this.entityData.defaultPrivacyOption || '';
				},
				set( value ) {
					this.isDefaultDirty = true
					this.isModified = true
					this.setEntityProp( 'defaultPrivacyOption', value )
				}
			},

			defaultSitePrivacy: {
				get() {
					return this.entityData.defaultSitePrivacyOption || '';
				},
				set( value ) {
					this.isSiteDefaultDirty = true
					this.isModified = true
					this.setEntityProp( 'defaultSitePrivacyOption', value )
				}
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

			siteTemplatesAdminUrl() {
				return window.CBOXOLStrings.siteTemplatesAdminUrl
			},

			supportsParent() {
				return this.entityData.hasOwnProperty( 'parent' )
			},

			typeSupportsDeletion() {
				return 'groupType' !== this.entityType
			}
		},

		data() {
			return {
				originalDefault: '',
				originalSiteDefault: '',
				isDefaultDirty: false,
				isSiteDefaultDirty: false
			};
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

			isDefaultTemplate: ( siteId ) => {
				return siteId === this.entityData.siteTemplate
			},

			privacyLabel( option ) {
				// Customize labels however you want, or localize from `this.strings`
				const labels = {
					public: this.strings.public,
					private: this.strings.private,
					hidden: this.strings.hidden
				};

				return labels[option] || option;
			},

			sitePrivacyLabel( option ) {
				return this.allSiteBlogPublicOptions.get( String( option ) ) || '';
			},

			togglePrivacy(option) {
				this.isModified = true;

				const current = this.selectedPrivacyOptions.slice(); // shallow copy

				const index = current.indexOf(option);
				if (index > -1) {
					current.splice(index, 1); // remove
				} else {
					current.push(option); // add
				}

				this.$store.commit( 'updateEntityPrivacyOptions', {
					key: this.itemsKey,
					slug: this.slug,
					options: current
				} );
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
						if ( '_new' === itemType.slug.substr( 0, 4 ) && ( 'academicUnitType' === itemType.entityType || 'academicTerm' === itemType.entityType ) ) {
							window.onbeforeunload = null
							window.location.reload()
							return
						}

						itemType.isModified = false
						itemType.isLoading = false

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

		mounted() {
			// Capture the initial default when the component mounts
			this.originalDefault = this.defaultPrivacy;
			this.originalSiteDefault = this.defaultSitePrivacy;
		},

		props: [
			'entityType',
			'isSortable',
			'isToggleable',
			'slug'
		],

		watch: {
			selectedPrivacyOptions: {
				handler( newOptions ) {
					const currentDefault = this.defaultPrivacy;
					const original = this.originalDefault;

					const currentIsValid = newOptions.includes( currentDefault );
					const originalIsValid = newOptions.includes( original );

					// Case 1: current default is invalid
					if ( ! currentIsValid ) {
						// Prefer restoring original if valid and user hasn't changed anything
						const newDefault = originalIsValid && ! this.isDefaultDirty ? original : newOptions[0] || '';
						if ( this.defaultPrivacy !== newDefault ) {
							this.setEntityProp( 'defaultPrivacyOption', newDefault );
						}
					}

					// Case 2: current is valid, but original just became valid again
					else if ( originalIsValid && ! this.isDefaultDirty && currentDefault !== original ) {
						if ( this.defaultPrivacy !== original ) {
							this.setEntityProp( 'defaultPrivacyOption', original );
						}
					}
				},
				deep: false,
				immediate: true
			},
			selectedSitePrivacyOptions: {
				handler( newOptions ) {
					const currentDefault = this.defaultSitePrivacy;
					const original = this.originalSiteDefault;

					const currentIsValid = newOptions.includes( currentDefault );
					const originalIsValid = newOptions.includes( original );

					// Case 1: current default is invalid
					if ( ! currentIsValid ) {
						const newDefault = originalIsValid && ! this.isSiteDefaultDirty ? original : newOptions[0] || '';

						if ( this.defaultSitePrivacy !== newDefault ) {
							this.setEntityProp( 'defaultSitePrivacyOption', newDefault );
						}
					}

					// Case 2: current is valid, but original just became valid again
					else if ( originalIsValid && ! this.isSiteDefaultDirty && currentDefault !== original ) {
						if ( this.defaultSitePrivacy !== original ) {
							this.setEntityProp( 'defaultSitePrivacyOption', original );
						}
					}
				},
				deep: false,
				immediate: true
			},
		}
	}
</script>
