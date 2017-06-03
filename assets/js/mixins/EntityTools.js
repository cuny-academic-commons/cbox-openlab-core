module.exports = {
	computed: {
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

		name: {
			get() { return this.getEntityProp( 'name' ) },
			set( value ) { this.setEntityProp( 'name', value ) }
		},
	},

	methods: {
		getEntityProp: function( prop ) {
			return this.$store.state[ this.itemsKey ][ this.slug ][ prop ]
		},

		setEntityProp: function( prop, value ) {
			if ( ! this.isModified ) {
				this.isModified = true
			}

			this.$store.commit( 'setEntityProperty', {
				itemsKey: this.itemsKey,
				property: prop,
				slug: this.slug,
				value: value
			} )
		}
	}
}
