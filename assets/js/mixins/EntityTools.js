import i18nTools from './i18nTools.js'

module.exports = {
	computed: {
		apiRoute() {
			return this.getEntityTypeProp( 'apiRoute' )
		},

		canBeDeleted() {
			return this.getEntityProp( 'id' )
		},

		id() {
			return this.getEntityProp( 'id' )
		},

		isCollapsed: {
			get() { return this.getEntityProp( 'isCollapsed' ) },
			set( value ) { this.setEntityProp( 'isCollapsed', value ) }
		},

		isEnabled: {
			get() { return this.getEntityProp( 'isEnabled' ) },
			set( value ) { this.setEntityProp( 'isEnabled', value ) }
		},

		isLoading: {
			get() { return this.getEntityProp( 'isLoading' ) },
			set( value ) { this.setEntityProp( 'isLoading', value ) }
		},

		isModified: {
			get() { return this.getEntityProp( 'isModified' ) },
			set( value ) {
				// Don't use setEntityProp() to avoid recursion.
				this.$store.commit( 'setEntityProperty', {
					itemsKey: this.itemsKey,
					property: 'isModified',
					slug: this.slug,
					value: value
				} )
			}
		},

		itemsKey() {
			return this.getEntityTypeProp( 'itemsKey' )
		},

		name: {
			get() { return this.getEntityProp( 'name' ) },
			set( value ) { this.setEntityProp( 'name', value ) }
		},

		namesKey() {
			return this.getEntityTypeProp( 'namesKey' )
		}
	},

	methods: {
		getEntityTypeProp: function( prop ) {
			const schema = {
				groupType: {
					addNewPlaceholder: this.strings.addNewType,
					apiRoute: 'item-type',
					itemsKey: 'types',
					namesKey: 'typeNames'
				},
				memberType: {
					addNewPlaceholder: this.strings.addNewType,
					apiRoute: 'item-type',
					itemsKey: 'types',
					namesKey: 'typeNames'
				},
				groupCategory: {
					addNewPlaceholder: this.strings.addNewType,
					apiRoute: 'group-category',
					itemsKey: 'types',
					namesKey: 'typeNames'
				},
			}

			return schema[ this.entityType ][ prop ]
		},

		getEntityProp: function( prop ) {
			return this.$store.state[ this.itemsKey ][ this.slug ][ prop ]
		},

		setEntityProp: function( prop, value ) {
			const nonDirtyProps = [ 'id', 'isCollapsed', 'isLoading', 'isModified' ]

			if ( ! this.isModified && -1 == nonDirtyProps.indexOf( prop ) ) {
				this.isModified = true
			}

			this.$store.commit( 'setEntityProperty', {
				itemsKey: this.itemsKey,
				property: prop,
				slug: this.slug,
				value: value
			} )
		}
	},

	mixins: [
		i18nTools
	],

	props: {
		entityType: {
			required: true,
			type: String
		}
	}
}
