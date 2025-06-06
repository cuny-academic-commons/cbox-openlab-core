/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/site-templates-admin.css":
/*!*********************************************!*\
  !*** ./assets/src/site-templates-admin.css ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************************************!*\
  !*** ./assets/src/site-templates-admin.js ***!
  \********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _site_templates_admin_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./site-templates-admin.css */ "./assets/src/site-templates-admin.css");
/* global ajaxurl, jQuery, SiteTemplatePickerAdmin */


(function ($) {
  const {
    endpoint,
    nonce
  } = window.SiteTemplatePickerAdmin;
  $(document).ready(() => {
    $('#template-site-id').select2({
      ajax: {
        url: endpoint + '?_wpnonce=' + nonce,
        dataType: 'json',
        data: params => {
          const query = {
            search: params.term,
            page: params.page || 1
          };
          return query;
        }
      }
    });
  });
  $(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
  });
  const templateCategoryCheckboxes = document.querySelectorAll('#cboxol_template_categorychecklist input[type="checkbox"]');
  if (templateCategoryCheckboxes.length) {
    calculateAllowedMemberTypeRestrictionsByGroupType();
    templateCategoryCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', calculateAllowedMemberTypeRestrictionsByGroupType);
    });
  }
  const visibilityRadios = document.querySelectorAll('.template-visibility-radios input[type="radio"]');
  const setVisibilitySuboptionsVisibility = () => {
    visibilityRadios.forEach(radio => {
      const controlsId = radio.getAttribute('aria-controls');
      const isSuboptionVisible = 'yes' === radio.value && radio.checked || 'no' === radio.value && !radio.checked;
      const suboptions = document.getElementById(controlsId);
      if (suboptions) {
        suboptions.style.display = isSuboptionVisible ? 'block' : 'none';
      }
    });
  };
  setVisibilitySuboptionsVisibility();
  visibilityRadios.forEach(radio => {
    radio.addEventListener('change', setVisibilitySuboptionsVisibility);
  });
  const academicUnitCheckboxes = document.querySelectorAll('#template-visibility-suboptions-academic-unit input[type="checkbox"]');
  const academicUnitCheckboxArray = [...academicUnitCheckboxes];

  // When an Academic Unit checkbox is checked, check and disable descendant checkboxes.
  const academicUnitClickHandler = e => {
    const checkbox = e.target;
    const slug = checkbox.dataset.slug;
    const isChecked = checkbox.checked;
    const toggleChildren = (parentSlug, toggleState) => {
      const children = academicUnitCheckboxArray.filter(child => child.dataset.parent === parentSlug);
      children.forEach(child => {
        child.checked = toggleState;
        toggleChildren(child.dataset.slug, toggleState);
      });
    };
    toggleChildren(slug, isChecked);
  };
  academicUnitCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('click', academicUnitClickHandler);
  });
  document.addEventListener('DOMContentLoaded', function () {
    const postsList = document.querySelectorAll('.wp-list-table tbody tr');
    let draggedItem = null;
    postsList.forEach(post => {
      post.draggable = true;
      post.addEventListener('dragstart', e => {
        draggedItem = e.target;
        if (draggedItem.tagName !== 'TR') {
          draggedItem = draggedItem.closest('tr');
        }
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', draggedItem);
        draggedItem.classList.add('cboxol-dragging');
      }, false);
      post.addEventListener('dragover', e => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const hoverTarget = e.target.tagName === 'TR' ? e.target : e.target.closest('tr');
        hoverTarget.classList.add('cboxol-dragover');
        hoverTarget.addEventListener('dragleave', () => {
          hoverTarget.classList.remove('cboxol-dragover');
        });
      }, false);
      post.addEventListener('drop', e => {
        const dropTarget = e.target.tagName === 'TR' ? e.target : e.target.closest('tr');
        if (draggedItem !== dropTarget) {
          draggedItem.parentNode.insertBefore(draggedItem, dropTarget.nextSibling || dropTarget);
          updatePostOrder();
          draggedItem.classList.add('cboxol-just-dropped');
          setTimeout(() => {
            draggedItem.classList.remove('cboxol-just-dropped');
          }, 1000);
        }

        // remove cbxol-dragover class from all tr elements
        document.querySelectorAll('tr').forEach(el => {
          el.classList.remove('cboxol-dragover');
          el.classList.remove('cboxol-dragging');
        });
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
    }).then(response => response.json()).then(() => {}).catch(() => {});
  }
  function calculateAllowedMemberTypeRestrictionsByGroupType() {
    const {
      categoryMap,
      courseCreateMemberTypes,
      courseGroupTypeSlug,
      lang
    } = SiteTemplatePickerAdmin;
    if (!categoryMap) {
      return;
    }
    const checkedCategories = Array.from(templateCategoryCheckboxes).filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
    let allowedGroupTypes = [];
    checkedCategories.forEach(category => {
      const groupTypes = categoryMap[category];
      if (groupTypes) {
        allowedGroupTypes = allowedGroupTypes.concat(groupTypes);
      }
    });

    // Remove duplicates
    allowedGroupTypes = [...new Set(allowedGroupTypes)];

    // We only restrict if there's a single allowed group type, and it's the courseGroupTypeSlug.
    const restrict = allowedGroupTypes.length === 1 && allowedGroupTypes[0] === courseGroupTypeSlug;
    const limitToMemberTypeCheckboxes = document.querySelectorAll('input.template-visibility-limit-to-member-types');
    const messageContainer = document.querySelector('.template-visibility-limit-to-member-types-message');
    if (restrict) {
      // Get the labels of the member types that can create courses from the limitToMemberTypeCheckboxes.
      const allowedMemberTypeLabels = [...limitToMemberTypeCheckboxes].filter(checkbox => courseCreateMemberTypes.includes(checkbox.value)).map(checkbox => checkbox.labels[0].textContent);
      const listFormatter = new Intl.ListFormat(lang, {
        style: 'long',
        type: 'conjunction'
      });
      const formattedList = listFormatter.format(allowedMemberTypeLabels);

      // Show the message.
      messageContainer.style.display = 'block';
      messageContainer.querySelector('.member-type-names').textContent = formattedList;

      // Disable all but those in allowedGroupTypes.
      limitToMemberTypeCheckboxes.forEach(checkbox => {
        checkbox.disabled = !courseCreateMemberTypes.includes(checkbox.value);
      });
    } else {
      // Hide the message.
      messageContainer.style.display = 'none';

      // Enable all checkboxes.
      limitToMemberTypeCheckboxes.forEach(checkbox => {
        checkbox.disabled = false;
      });
    }
  }
})(jQuery);
})();

/******/ })()
;
//# sourceMappingURL=site-templates-admin.js.map