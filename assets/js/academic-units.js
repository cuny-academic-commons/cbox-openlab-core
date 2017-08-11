/**
 * JS functionality for Academic Unit frontend selector.
 */
(function($){

	var $accountTypeSelector,
		$academicUnitCheckboxes,
		$academicUnits;

	$(document).ready(function() {
		$accountTypeSelector = $('#account-type');
		if ( ! $accountTypeSelector.length ) {
			$accountTypeSelector = $('#member-type');
		}

		showAcademicUnitTypesForMemberType( $accountTypeSelector.val() );
		$accountTypeSelector.change( function() {
			showAcademicUnitTypesForMemberType( this.value );
		} );

		$academicUnits = $('.academic-unit');
		$academicUnitCheckboxes = $('.academic-unit-checkbox');
		validateAcademicTypeSelector();
		$academicUnitCheckboxes.change( validateAcademicTypeSelector );
	});

	/**
	 * Hide/show unit types based on selected member type.
	 */
	function showAcademicUnitTypesForMemberType( memberType ) {
		$('.cboxol-academic-unit-selector-for-type').hide();
		if ( CBOXOLAcademicTypes.typesByMemberType.hasOwnProperty( memberType ) ) {
			for ( var i in CBOXOLAcademicTypes.typesByMemberType[ memberType ] ) {
				$('.cboxol-academic-unit-selector-for-type-' + CBOXOLAcademicTypes.typesByMemberType[ memberType ][ i ] ).show();
			}
		}
	}

	/**
	 * Hide/show units based on whether the parent is selected.
	 */
	function validateAcademicTypeSelector() {
		var $selectedUnits = $('.academic-unit-checkbox:checked');
		var selectedUnitSlugs = [];
		$selectedUnits.each( function( k, v	) {
			selectedUnitSlugs.push( v.value );
		} );

		$academicUnits.removeClass( 'academic-unit-visible' ).addClass( 'academic-unit-hidden' );
		$academicUnitCheckboxes.each( function( k, v ) {
			// Items without parents or with unchecked parents should be shown.
			var hasParent = v.dataset.hasOwnProperty( 'parent' ) && v.dataset.parent.length > 0;
			if ( ! hasParent || -1 !== selectedUnitSlugs.indexOf( v.dataset.parent ) ) {
				$( v ).closest( '.academic-unit' ).removeClass( 'academic-unit-hidden' ).addClass( 'academic-unit-visible' );
			} else {
				// Hidden fields can't be checked.
				$( v ).prop( 'checked', false );
			}
		} );
	}
}(jQuery))
