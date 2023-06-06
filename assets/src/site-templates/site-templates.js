/**
 * Internal dependencies
 */
import { getSiteTemplates } from './api';

import './site-template-picker.scss'

const templateCategories = document.querySelector( '#site-template-categories' );
const templatePicker = document.querySelector( '.site-template-picker' );
const templatePanel = document.querySelector( '.panel-template-picker' );
const templatePagination = document.querySelector( '.site-template-pagination' );
const templateToClone = document.querySelector( '[name="source_blog"]' );
const setupSiteToggle = document.querySelector( '#set-up-site-toggle' );
const siteType = document.querySelectorAll( '[name="new_or_old"]' );
const messages = window.SiteTemplatePicker.messages;
const defaultMap = window.SiteTemplatePicker.defaultMap;
const currentGroupType = window.CBOXOL_Group_Create?.new_group_type || null

// Cache default template. Usually it's group type site template.
const defaultTemplateForGroupType = currentGroupType && defaultMap.hasOwnProperty( currentGroupType ) ? defaultMap[ currentGroupType ] : 0

const defaultTemplate = defaultTemplateForGroupType ? defaultTemplateForGroupType.toString() : templateToClone.value

function renderTemplate( { id, siteId, title, excerpt, image, categories } ) {
	return `
	<button type="button" class="site-template-component" data-template-id="${ id }" data-template-site-id="${ siteId }">
		<div class="site-template-component__image">
			${ image
				? `<img src="${ image }" alt="${ title }">`
				: `<svg fill="currentColor" width="24" height="24" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>`
			}
			<div class="site-template-component__description">${ excerpt }</div>
		</div>
		<div class="site-template-component__meta">
			<span class="site-template-component__category">${ categories.join( ', ' ) }</span>
			<div class="site-template-component__name">${ title }</div>
		</div>
	</button>
	`;
}

function updateTemplates( category, page ) {
	templatePicker.innerHTML = `<p>${ messages.loading }</p>`;

	getSiteTemplates( category, page ).then( ( { templates, prev, next } ) => {

		if ( ! templates.length ) {
			templatePicker.innerHTML = `<p>${ messages.noResults }</p>`;
			return;
		}

		const compiled = templates.map( ( template ) => renderTemplate( template ) ).join('');
		templatePicker.innerHTML = compiled;

		// Restore template to default value.
		setSelectedTemplateId( defaultTemplate )

		updatePagination( prev, next );
	} );
}

function updatePagination( prev, next ) {
	const prevBtn = templatePagination.querySelector( '.prev' );
	const nextBtn = templatePagination.querySelector( '.next' );

	const isVisible = templatePagination.classList.contains( 'hidden' );
	const hide = ! prev && ! next && ! isVisible;

	// Hide pagination if we have only one page.
	if ( hide ) {
		templatePagination.classList.add( 'hidden' );
	}

	// Button are enabled later if we have pages.
	prevBtn.disabled = true;
	nextBtn.disabled = true;

	if ( prev ) {
		prevBtn.dataset.page = prev;
		prevBtn.disabled = false;
	}

	if ( next ) {
		nextBtn.dataset.page = next;
		nextBtn.disabled = false;
	}
}

function togglePanel( display = false ) {
	if ( display ) {
		// Don't unhide if there's 0 or 1 templates to show.
		const templates = templatePicker.querySelectorAll( '.site-template-component' );
		if ( templates.length > 1 ) {
			templatePanel.classList.remove( 'hidden' );
		}
		return;
	}

	templatePanel.classList.add( 'hidden' );

	// Restore template to default value.
	setSelectedTemplateId( defaultTemplate )
}

function setSelectedTemplateId( selectedId ) {
	const templates = templatePicker.querySelectorAll( '.site-template-component' );

	templates.forEach( ( template ) => {
		const templateId = template.dataset.templateId;

		if ( templateId === selectedId ) {
			template.classList.add( 'is-selected' )

			templateToClone.value = template.dataset.templateSiteId;
		} else {
			template.classList.remove( 'is-selected' )
		}
	} )
}

templateCategories.addEventListener( 'change', function( event ) {
	const category = ( event.target.value !== '0' ) ? event.target.value : null;

	templatePicker.innerHTML = `<p>${ messages.loading }</p>`;

	updateTemplates( category );
} )

templatePicker.addEventListener( 'click', function( event ) {
	const target = event.target.closest( '.site-template-component' );

	if ( ! target ) {
		return;
	}

	setSelectedTemplateId( target.dataset.templateId )
} );

templatePicker.addEventListener( 'mouseover', function( event ) {
	const template = event.target.closest( '.site-template-component' );

	if ( ! template ) {
		return;
	}

	// Not using toggle since this event does bubble.
	if ( ! template.classList.contains( 'has-hover' ) ) {
		template.classList.add( 'has-hover' );
	}
} );

templatePicker.addEventListener( 'mouseout', function( event ) {
	const template = event.target.closest( '.site-template-component' );

	if ( ! template ) {
		return;
	}

	// Not using toggle since this event does bubble.
	if ( template.classList.contains( 'has-hover' ) ) {
		template.classList.remove( 'has-hover' );
	}
} );

templatePagination.addEventListener( 'click', function( event ) {
	const target = event.target.closest( '.btn' );

	if ( ! target ) {
		return;
	}

	const category = ( templateCategories.value !== '0' ) ? templateCategories.value : null;
	const page = target.dataset.page ? Number( target.dataset.page ) : null;

	updateTemplates( category, page );
} );

siteType.forEach( ( typeSelect ) => {
	typeSelect.addEventListener( 'change', ( event ) => togglePanel( event.target.value === 'new' ) );
} );

if ( setupSiteToggle ) {
	setupSiteToggle.addEventListener( 'change', ( event ) => togglePanel( event.target.checked ) );

	if ( setupSiteToggle.checked ) {
		// Display the panel.
		togglePanel( templatePanel.checked );
	}
} else {
	// If the setupSiteToggle doesn't exist, it means that sites are required for this group type.
	togglePanel( true );
}

// Prefetch templates.
updateTemplates();
