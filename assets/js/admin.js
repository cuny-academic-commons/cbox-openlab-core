import Vue from 'vue'
import Vuex from 'vuex'
import fetch from 'isomorphic-fetch'
import TypesUI from './components/TypesUI.vue'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		types: CBOXOL_Types
	},
	actions: {
		submitForm ( commit, payload ) {
			const typeData = commit.state.types[ payload.slug ]
			const endpoint = 'http://boone.cool/neh/wp-json/cboxol/v1/item-type/' + typeData.id
			const nonce = CBOXOLStrings.nonce

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
	render: h => h('app')
} );
