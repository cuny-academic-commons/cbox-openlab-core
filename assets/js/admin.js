import Vue from 'vue'
import Vuex from 'vuex'
import TypesUI from './components/TypesUI.vue'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		types: CBOXOL_Types,
		collapsed: [],
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
