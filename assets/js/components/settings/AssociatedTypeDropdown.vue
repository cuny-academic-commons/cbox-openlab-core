<template>
	<select
		:id="fieldId"
		:name="fieldId" v-model="settingValue"
	>
		<option value=""> -- </option>
		<option value="optional">{{ strings.optional }}</option>
		<option value="required">{{ strings.required }}</option>
	</select>
</template>

<script>
	import EntityTools from '../../mixins/EntityTools.js'
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			settingValue: {
				get() {
					const allEntityItemTypes = this.getEntityProp( this.associatedType )

					let value = ''
					if ( allEntityItemTypes.hasOwnProperty( this.associatedTypeSlug ) ) {
						value = allEntityItemTypes[ this.associatedTypeSlug ]
					}

					return value
				},

				set( value ) {
					const oldAssociatedTypes = this.getEntityProp( this.associatedType )

					let newAssociatedTypes = Object.assign( {}, oldAssociatedTypes )
					newAssociatedTypes[ this.associatedTypeSlug ] = value

					this.setEntityProp( this.associatedType, newAssociatedTypes )
				}
			}
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'associatedType',
			'associatedTypeSlug',
			'entityType',
			'fieldId',
			'slug'
		]
	}
</script>
