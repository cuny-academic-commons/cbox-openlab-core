(function(){
	document.addEventListener( 'DOMContentLoaded', () => {
		const { defaultCategoryId } = window.SiteTemplatesDefaultCategory

		if ( ! defaultCategoryId ) {
			return
		}

		const defaultCategoryCheckbox = document.querySelector( '#in-cboxol_template_category-' + defaultCategoryId )

		if ( ! defaultCategoryCheckbox ) {
			return
		}

		defaultCategoryCheckbox.checked = true
	} )
})()
