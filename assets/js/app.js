import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
import CBOXOLAdmin from './components/CBOXOLAdmin.vue'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		dummy: {},
		emailDomains: {},
		groupCategories: {},
		groupCategoryNames: [],
		isEditing: {},
		isLoading: {},
		memberTypes: [],
		objectType: '',
		signupCodes: {},
		strings: CBOXOLStrings.strings,
		subapp: '',
		types: {},
		typeNames: []
	},
	actions: {
		submitDeleteEntity ( commit, payload ) {
			const { apiRoute, id } = payload

			const nonce = CBOXOLStrings.nonce
			const endpoint = CBOXOLStrings.endpointBase + apiRoute + '/' + id

			return fetch( endpoint, {
				method: 'DELETE',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				}
			} )
		},
		submitDeleteEmailDomain ( commit, payload ) {
			const { domain } = payload

			let endpoint = CBOXOLStrings.endpointBase + 'email-domain/' + domain

			return fetch( endpoint, {
				method: 'DELETE',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': CBOXOLStrings.nonce
				}
			} )
		},
		submitDeleteSignupCode ( commit, payload ) {
			const { wpPostId } = payload

			let endpoint = CBOXOLStrings.endpointBase + 'signup-code/' + wpPostId

			return fetch( endpoint, {
				method: 'DELETE',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': CBOXOLStrings.nonce
				}
			} )
		},
		submitEmailDomain ( commit, payload ) {
			const { domain } = payload

			let endpoint = CBOXOLStrings.endpointBase + 'email-domain/'

			const body = { domain }

			return fetch( endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': CBOXOLStrings.nonce
				},
				body: JSON.stringify( body )
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
		},
		submitSignupCode ( commit, payload ) {
			const { wpPostId } = payload

			let endpoint = CBOXOLStrings.endpointBase + 'signup-code/'
			if ( wpPostId ) {
				endpoint += wpPostId
			}

			return fetch( endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': CBOXOLStrings.nonce
				},
				body: JSON.stringify( payload )
			} )
		},
	},
	mutations: {
		addNewEntity ( state, payload ) {
			const { itemsKey, namesKey } = payload

			// Get unique key.
			let isAvailable = false
			let baseKey = '_new'
			let key = baseKey
			let incr = 1

			do {
				if ( state[ itemsKey ].hasOwnProperty( key ) ) {
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
			state[ itemsKey ][ key ] = dummy

			// Push to typeNames to force render.
			state[ namesKey ].push( key )
		},

		orderEntities ( state, payload ) {
			const { itemsKey, namesKey } = payload

			let newEntityNames = state[ namesKey ]
			console.log(newEntityNames)

			newEntityNames.sort( function( a, b ) {
				const order_a = state[ itemsKey ][ a ].settings.Order.data
				const order_b = state[ itemsKey ][ b ].settings.Order.data

				if ( order_a == order_b ) {
					return 0
				}

				return order_a > order_b
			} )

			state[ namesKey ] = newEntityNames
		},

		removeEmailDomain ( state, payload ) {
			const { domain } = payload

			let newEmailDomains = Object.assign( {}, state.emailDomains )
			delete newEmailDomains[ domain ]

			state.emailDomains = newEmailDomains
		},

		removeSignupCode ( state, payload ) {
			const { wpPostId } = payload

			let newSignupCodes = Object.assign( {}, state.signupCodes )
			delete newSignupCodes[ wpPostId ]

			state.signupCodes = newSignupCodes
		},

		removeType ( state, payload ) {
			var index = state.typeNames.indexOf( payload.slug )
			if ( index > -1 ) {
				state.typeNames.splice( index, 1 )
			}

			delete state.types[ payload.slug ]
		},

		setEmailDomain( state, payload ) {
			const { key, domain } = payload
			let newEmailDomains = Object.assign( {}, state.emailDomains )
			newEmailDomains[ key ] = domain

			state.emailDomains = newEmailDomains
		},

		setIsEditing( state, payload ) {
			const { key, value } = payload

			let newIsEditing = Object.assign( {}, state.isEditing )

			if ( value && ! newIsEditing.hasOwnProperty( key ) ) {
				newIsEditing[ key ] = true
			} else if ( ! value && newIsEditing.hasOwnProperty( key ) ) {
				delete newIsEditing[ key ]
			}

			state.isEditing = newIsEditing
		},

		setIsLoading( state, payload ) {
			const { key, value } = payload

			let newIsLoading = Object.assign( {}, state.isLoading )

			if ( value && ! newIsLoading.hasOwnProperty( key ) ) {
				newIsLoading[ key ] = true
			} else if ( ! value && newIsLoading.hasOwnProperty( key ) ) {
				delete newIsLoading[ key ]
			}

			state.isLoading = newIsLoading
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

		setSignupCode( state, payload ) {
			const { key, signupCode } = payload
			let newSignupCodes = Object.assign( {}, state.signupCodes )
			newSignupCodes[ key ] = signupCode

			state.signupCodes = newSignupCodes
		},

		setSignupCodeProperty ( state, payload ) {
			const { wpPostId, field, value } = payload

			let signupCode = Object.assign( {}, state.signupCodes[ wpPostId ] )

			switch ( field ) {
				// The member type "name" must always be updated to match slug.
				case 'memberTypeSlug' :
					signupCode.memberType.slug = value

					if ( state.memberTypes.hasOwnProperty( value ) ) {
						signupCode.memberType.name = state.memberTypes[ value ].label
					} else {
						signupCode.memberType.name = ''
					}

				break;

				case 'group' :
				case 'code' :
				default :
					signupCode[ field ] = value

				break;
			}

			let newSignupCodes = Object.assign( {}, state.signupCodes )
			newSignupCodes[ wpPostId ] = signupCode
			state.signupCodes = newSignupCodes
		},

		setEntityProperty ( state, payload ) {
			const { itemsKey, property, slug, value } = payload

			let newEntity = Object.assign( {}, state[ itemsKey ][ slug ] )
			newEntity[ property ] = value

			let newEntities = Object.assign( {}, state[ itemsKey ] )
			newEntities[ slug ] = newEntity

			state[ itemsKey ] = newEntities

			if ( 'isModified' == property ) {
				if ( value ) {
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

		setUpEntityNames ( state, payload ) {
			const { itemsKey, namesKey } = payload

			let entityName
			for ( entityName in state[ itemsKey ] ) {
				if ( state[ itemsKey ].hasOwnProperty( entityName ) ) {
					state[ namesKey ].push( entityName )
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
