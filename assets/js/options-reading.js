/* global cboxolOptionsReading */
(function(){
	const { availableSitePrivacyOptions } = cboxolOptionsReading;
	const blogPublicRadios = document.querySelectorAll('input[name="blog_public"]');

	// Stupid special case: If neither 1 or 0 is in the allowed list, remove the p.description element in the fieldset.
	const description = blogPublicRadios[0].closest('fieldset').querySelector('p.description');
	if (description) {
		const hasOne = availableSitePrivacyOptions.includes('1');
		const hasZero = availableSitePrivacyOptions.includes('0');

		if (!hasOne && !hasZero) {
			description.remove();
		}
	}

	// Remove those with a value that's not in availableSitePrivacyOptions
	blogPublicRadios.forEach((radio) => {
		// Compare as strings.
		const radioValue = String(radio.value);

		if (!availableSitePrivacyOptions.includes(radioValue)) {
			// remove the radio itself as well as the label
			const radioId = radio.id;
			const label = document.querySelector(`label[for="${radioId}"]`);
			if (label) {
				label.remove();
			}
			radio.remove();
		}
	})
}())
