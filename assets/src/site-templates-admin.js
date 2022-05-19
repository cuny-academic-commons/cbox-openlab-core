(function($){
	const { endpoint, nonce } = window.SiteTemplatePickerAdmin

	$(document).ready(() => {
		$( '#template-site-id' )
			.select2({
				ajax: {
					url: endpoint + '?_wpnonce=' + nonce,
					dataType: 'json',
					data: function (params) {
						const query = {
							search: params.term,
							page: params.page || 1
						}

						return query;
					}
				}
			});
	})
}(jQuery))
