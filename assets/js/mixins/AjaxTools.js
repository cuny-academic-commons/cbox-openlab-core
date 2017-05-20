module.exports = {
	methods: {
		ajaxError( p ) {
			// @todo better error handling
			console.error( p )
			throw 'Could not complete request.'
		},
		checkStatus(response) {
			if (response.status >= 200 && response.status < 300) {
				return response
			} else {
				var error = new Error(response.statusText)
				error.response = response
				throw error
			}
		},

		parseJSON(response) {
			return response.json()
		}
	}
}
