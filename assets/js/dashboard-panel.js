/* global ajaxurl, CBOXOLDashboardPanel */

// Create 'Screen options' checkbox for OpenLab News panel.
const { backgroundColor, panelIsVisible, textColor } = CBOXOLDashboardPanel;
const checkboxEl = document.createElement( 'input' );
checkboxEl.setAttribute( 'type', 'checkbox' );
checkboxEl.setAttribute( 'id', 'openlab_news_panel-hide' );
checkboxEl.checked = panelIsVisible;

const checkboxLabelEl = document.createElement( 'label' );
checkboxLabelEl.setAttribute( 'for', 'openlab_news_panel-hide' );
checkboxLabelEl.textContent = 'OpenLab News'; // @todo i18n

checkboxLabelEl.prepend( checkboxEl );

const panelHeader = document.querySelector( '.openlab-news-panel-content .panel-header' );
if ( panelHeader ) {
	panelHeader.style.backgroundColor = backgroundColor;
	panelHeader.style.color = textColor;
}

const wpWelcomePanelHide = document.querySelector( 'label[for="wp_welcome_panel-hide"]' );
if ( wpWelcomePanelHide ) {
	wpWelcomePanelHide.after( checkboxLabelEl );
} else {
	const metaboxPrefs = document.querySelector( '#screen-options-wrap .metabox-prefs' );
	metaboxPrefs.append( checkboxLabelEl );
}

// The OpenLab News panel should appear just before #dashboard-widgets-wrap.
const openlabNewsPanel = document.querySelector( '#openlab-news-panel' );
const dashboardWidgetsWrap = document.querySelector( '#dashboard-widgets-wrap' );
dashboardWidgetsWrap.before( openlabNewsPanel );

// Add a Dismiss link to the panel.
const openlabNewsPanelDismiss = document.createElement( 'a' );
openlabNewsPanelDismiss.setAttribute( 'href', '#' );
openlabNewsPanelDismiss.setAttribute( 'class', 'panel-dismiss' );
openlabNewsPanelDismiss.textContent = 'Dismiss'; // @todo i18n
openlabNewsPanel.prepend( openlabNewsPanelDismiss );
openlabNewsPanelDismiss.addEventListener( 'click', ( e ) => {
	e.preventDefault();
	openlabNewsPanelDismissHandler( false );
} );

// Hide OpenLab News panel if checkbox is checked.
const openlabNewsPanelShow = document.querySelector( '#openlab_news_panel-hide' );
openlabNewsPanelShow.addEventListener( 'change', () => {
	openlabNewsPanelDismissHandler( openlabNewsPanelShow.checked );
} );

const openlabNewsPanelDismissHandler = ( isVisible ) => {
	if ( isVisible ) {
		openlabNewsPanel.classList.remove( 'hidden' );
	} else {
		openlabNewsPanel.classList.add( 'hidden' );
	}

	// Send an AJAX request to save this setting.
	const data = {
		action: 'cboxol_hide_dashboard_panel',
		visible: isVisible,
		nonce: document.getElementById( 'openlab-news-panel-nonce' ).value,
	};

	fetch( ajaxurl, {
		method: 'POST',
		body: new URLSearchParams( data )
	} );
}
