/* global tinymce, QTags */
(() => {
	const form = document.querySelector( '#dashboard-panel-settings-form' );
	if ( ! form ) {
		return;
	}

	const disableableEditorIds = [ 'tagline' ];

	const enabledCheckbox = form.querySelector( 'input[name="enabled"]' );
	const disableableFields = form.querySelectorAll( '.disabled-when-disabled' );

	const maybeDisableOptions = () => {
		const isEnabled = enabledCheckbox.checked;

		form.classList.toggle( 'feature-disabled', !isEnabled );

		disableableFields.forEach((field) => {
			// Disable regular inputs
			if (field.tagName !== 'TEXTAREA') {
				field.disabled = !isEnabled;
			}
		})

		disableableEditorIds.forEach((editorId) => {
			// TinyMCE
			if (window.tinymce && tinymce.get(editorId)) {
				const editor = tinymce.get(editorId);
				if (isEnabled) {
					editor.setMode('design');
				} else {
					editor.setMode('readonly');
				}
			}

			// Quicktags (Text tab)
			if (window.QTags && QTags.instances?.[editorId]) {
				const qtEditor = QTags.instances[editorId];
				if (qtEditor) {
					const textarea = document.getElementById(editorId);
					if (textarea) {
						textarea.disabled = !isEnabled;
					}
				}
			}
		});
	};

	function onAllEditorsReady(editorIds, callback) {
		const waitForGlobals = () => {
			if (typeof window.tinymce === 'undefined' || typeof window.QTags === 'undefined') {
				setTimeout(waitForGlobals, 50);
				return;
			}

			let readyCount = 0;
			const total = editorIds.length;

			const markReady = () => {
				readyCount++;
				if (readyCount === total) {
					callback();
				}
			};

			editorIds.forEach((editorId) => {
				let tinymceHandled = false;
				let quicktagsHandled = false;

				const checkReady = () => {
					if (tinymceHandled && quicktagsHandled) {
						markReady();
					}
				};

				// TinyMCE
				const maybeInitTinyMCE = () => {
					const editor = tinymce.get(editorId);
					if (!editor) {
						setTimeout(maybeInitTinyMCE, 50);
						return;
					}
					if (editor.initialized) {
						tinymceHandled = true;
						checkReady();
					} else {
						editor.on('init', () => {
							tinymceHandled = true;
							checkReady();
						});
					}
				};
				maybeInitTinyMCE();

				// Quicktags (Text editor)
				const maybeInitQuicktags = () => {
					if (QTags.instances?.[editorId]) {
						quicktagsHandled = true;
						checkReady();
					} else {
						setTimeout(maybeInitQuicktags, 50);
					}
				};
				maybeInitQuicktags();
			});
		};

		waitForGlobals();
	}

	onAllEditorsReady(disableableEditorIds, maybeDisableOptions);
	enabledCheckbox.addEventListener( 'change', () => { maybeDisableOptions() } );
})();
