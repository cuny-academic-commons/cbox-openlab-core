import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
import TypesUI from './components/TypesUI.vue'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		types: CBOXOL_Types,
		typeNames: []
	},
	actions: {
		submitDelete ( commit, payload ) {
			const nonce = CBOXOLStrings.nonce
			const endpoint = CBOXOLStrings.endpoint + payload.id

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
			const nonce = CBOXOLStrings.nonce

			let endpoint = CBOXOLStrings.endpoint
			if ( typeData.id > 0 ) {
				endpoint += typeData.id
			}

			return fetch( endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				},
				body: JSON.stringify( typeData )
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
			let dummy = JSON.parse( JSON.stringify( CBOXOL_Dummy ) )
			dummy.slug = key
			dummy.isCollapsed = false
			state.types[ key ] = dummy

			// Push to typeNames to force render.
			state.typeNames.push( key )
		},

		removeType ( state, payload ) {
			var index = state.typeNames.indexOf( payload.slug )
			if ( index > -1 ) {
				state.typeNames.splice( index, 1 )
			}

			delete state.types[ payload.slug ]
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
		},

		setSelectableTypes ( state, payload ) {
			state.types[ payload.slug ].settings.MayChangeMemberTypeTo.data.selectableTypes = payload.selectableTypes
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
	el: '#cboxol-types-admin',
	store,
	components: {
		app: TypesUI
	},
	mounted() {
		this.$store.commit( 'setUpTypeNames' )
	},
	render: h => h('app')
} );
