/* global ajaxurl, jQuery */

import './site-templates-admin.css'

(function($){
	const { endpoint, nonce } = window.SiteTemplatePickerAdmin

	$(document).ready(() => {
		$( '#template-site-id' )
			.select2({
				ajax: {
					url: endpoint + '?_wpnonce=' + nonce,
					dataType: 'json',
					data: (params) => {
						const query = {
							search: params.term,
							page: params.page || 1
						}

						return query;
					}
				}
			});
	})

	$(document).on( 'select2:open', () => {
		document.querySelector( '.select2-search__field').focus()
	})

	const visibilityRadios = document.querySelectorAll('.template-visibility-radios input[type="radio"]');

	const setVisibilitySuboptionsVisibility = () => {
		visibilityRadios.forEach(radio => {
			const controlsId = radio.getAttribute('aria-controls');
			const isSuboptionVisible = 'yes' === radio.value && radio.checked || 'no' === radio.value && ! radio.checked;

			const suboptions = document.getElementById( controlsId )
			if ( suboptions ) {
				suboptions.style.display = isSuboptionVisible ? 'block' : 'none';
			}
		})
	}
	setVisibilitySuboptionsVisibility()

	visibilityRadios.forEach(radio => {
		radio.addEventListener('change', setVisibilitySuboptionsVisibility)
	})

	const academicUnitCheckboxes = document.querySelectorAll( '#template-visibility-suboptions-academic-unit input[type="checkbox"]' );
	const academicUnitCheckboxArray = [ ...academicUnitCheckboxes ];

	// When an Academic Unit checkbox is checked, check and disable descendant checkboxes.
	const academicUnitClickHandler = (e) => {
		const checkbox = e.target;
		const slug = checkbox.dataset.slug;
		const isChecked = checkbox.checked;

		const toggleChildren = (parentSlug, toggleState) => {
			const children = academicUnitCheckboxArray.filter( child => child.dataset.parent === parentSlug );
			children.forEach( child => {
				child.checked = toggleState;
				toggleChildren( child.dataset.slug, toggleState );
			} )
		}

		toggleChildren( slug, isChecked );
	}

	academicUnitCheckboxes.forEach( checkbox => {
		checkbox.addEventListener( 'click', academicUnitClickHandler )
	} )

	document.addEventListener('DOMContentLoaded', function() {
		const postsList = document.querySelectorAll('.wp-list-table tbody tr');
		let draggedItem = null;

		postsList.forEach(post => {
			post.draggable = true;

			post.addEventListener('dragstart', (e) => {
				draggedItem = e.target;
				if ( draggedItem.tagName !== 'TR' ) {
					draggedItem = draggedItem.closest('tr');
				}
				e.dataTransfer.effectAllowed = 'move';
				e.dataTransfer.setData('text/html', draggedItem);
				draggedItem.classList.add('cboxol-dragging');
			}, false);

			post.addEventListener('dragover', (e) => {
				e.preventDefault();
				e.dataTransfer.dropEffect = 'move';

				const hoverTarget = e.target.tagName === 'TR' ? e.target : e.target.closest('tr');
				hoverTarget.classList.add( 'cboxol-dragover' );
				hoverTarget.addEventListener('dragleave', () => {
					hoverTarget.classList.remove( 'cboxol-dragover' );
				})
			}, false);

			post.addEventListener('drop', (e) => {
				const dropTarget = e.target.tagName === 'TR' ? e.target : e.target.closest('tr');

				if ( draggedItem !== dropTarget ) {
					draggedItem.parentNode.insertBefore(draggedItem, dropTarget.nextSibling || dropTarget);
					updatePostOrder();

					draggedItem.classList.add( 'cboxol-just-dropped' )
					setTimeout(() => {
						draggedItem.classList.remove( 'cboxol-just-dropped' )
					}, 1000)
				}

				// remove cbxol-dragover class from all tr elements
				document.querySelectorAll( 'tr' ).forEach( el => {
					el.classList.remove('cboxol-dragover')
					el.classList.remove('cboxol-dragging')
				} );
			}, false);
		});

	});

	function updatePostOrder() {
		const posts = document.querySelectorAll('.wp-list-table tbody tr');
		const orderData = Array.from(posts).map((post, index) => {
			const postId = post.id.replace('post-', '');
			return {
				id: postId,
				position: index + 1
			};
		});

		const params = new URLSearchParams();
		params.append('order', JSON.stringify(orderData));
		params.append('security', nonce);

		fetch(ajaxurl + '?action=cboxol_update_site_template_order', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: params
		}).then(response => response.json())
		  .then(() => {})
		  .catch(error => console.error('Error updating order:', error));
	}
}(jQuery))
