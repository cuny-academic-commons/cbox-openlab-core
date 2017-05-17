import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
import CBOXOLAdmin from './components/CBOXOLAdmin.vue'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		dummy: {},
		isLoading: {},
		objectType: '',
		subapp: '',
		types: {},
		typeNames: []
	},
	actions: {
		submitDelete ( commit, payload ) {
			const nonce = CBOXOLStrings.nonce
			const endpoint = CBOXOLStrings.endpointBase + 'item-type/' + payload.id

			return fetch( endpoint, {
				method: 'DELETE',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				}
			} )
		},
		submitForm ( commit, payload ) {
			const typeData = commit.state.types[ payload.slug ]

			let endpoint = CBOXOLStrings.endpointBase + 'item-type/'
			if ( typeData.id > 0 ) {
				endpoint += typeData.id
			}

			const body = {
				typeData,
				objectType: commit.state.objectType
			}

			return fetch( endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': CBOXOLStrings.nonce
				},
				body: JSON.stringify( body )
			} )
		}
	},
	mutations: {
		addNewType ( state ) {
			// Get unique key.
			let isAvailable = false
			let baseKey = '_new'
			let key = baseKey
			let incr = 1

			do {
				if ( state.types.hasOwnProperty( key ) ) {
					key = baseKey + incr
					incr++
				} else {
					isAvailable = true
				}
			} while ( ! isAvailable )

			// Clone dummy data to that key.
			let dummy = JSON.parse( JSON.stringify( state.dummy ) )
			dummy.slug = key
			dummy.isCollapsed = false
			state.types[ key ] = dummy

			// Push to typeNames to force render.
			state.typeNames.push( key )
		},

		orderTypes ( state ) {
			state.typeNames.sort( function( a, b ) {
				const order_a = state.types[ a ].settings.Order.data
				const order_b = state.types[ b ].settings.Order.data

				if ( order_a == order_b ) {
					return 0
				}

				return order_a > order_b
			} )
		},

		removeType ( state, payload ) {
			var index = state.typeNames.indexOf( payload.slug )
			if ( index > -1 ) {
				state.typeNames.splice( index, 1 )
			}

			delete state.types[ payload.slug ]
		},

		setIsLoading( state, payload ) {
			const { key, value } = payload

			if ( value && ! state.isLoading.hasOwnProperty( key ) ) {
				state.isLoading[ key ] = true
			} else if ( ! value && state.isLoading.hasOwnProperty( key ) ) {
				delete state.isLoading[ key ]
			}
		},

		setMayCreateCourses ( state, payload ) {
			state.types[ payload.slug ].settings.MayCreateCourses.data = payload.value === 'yes'
		},

		setLabel ( state, payload ) {
			state.types[ payload.typeSlug ].labels[ payload.labelSlug ].value = payload.value
		},

		setOrder ( state, payload ) {
			state.types[ payload.slug ].settings.Order.data = payload.value
		},

		setTypeProperty ( state, payload ) {
			state.types[ payload.slug ][ payload.property ] = payload.value

			if ( 'isModified' == payload.property ) {
				if ( payload.value ) {
					window.onbeforeunload = function() { return true }
				} else {
					window.onbeforeunload = null
				}
			}
		},

		setSelectableTypes ( state, payload ) {
			state.types[ payload.slug ].settings.MayChangeMemberTypeTo.data.selectableTypes = payload.selectableTypes
		},

		setUpConfig ( state, payload ) {
			var prop
			for ( prop in payload ) {
				if ( state.hasOwnProperty( prop ) ) {
					state[ prop ] = payload[ prop ]
				}
			}
		},

		setUpTypeNames ( state ) {
			var typeName
			for ( typeName in state.types ) {
				if ( state.types.hasOwnProperty( typeName ) ) {
					state.typeNames.push( typeName )
				}
			}
		},

		toggleCollapsed ( state, payload ) {
			state.types[ payload.slug ].isCollapsed = ! state.types[ payload.slug ].isCollapsed
		}
	}
})

new Vue( {
	el: '#cboxol-admin',
	store,
	components: {
		app: CBOXOLAdmin
	},
	mounted() {
		this.$store.commit( 'setUpConfig', CBOXOL_AppConfig )
	},
	render: h => h('app')
} );
