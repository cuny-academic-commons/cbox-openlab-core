/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************************************!*\
  !*** ./assets/src/site-templates-default-category.js ***!
  \*******************************************************/
(function () {
  document.addEventListener('DOMContentLoaded', () => {
    const {
      defaultCategoryId
    } = window.SiteTemplatesDefaultCategory;

    if (!defaultCategoryId) {
      return;
    }

    const defaultCategoryCheckbox = document.querySelector('#in-cboxol_template_category-' + defaultCategoryId);

    if (!defaultCategoryCheckbox) {
      return;
    }

    defaultCategoryCheckbox.checked = true;
  });
})();
/******/ })()
;
//# sourceMappingURL=site-templates-default-category.js.map