(()=>{"use strict";!function(e){const{endpoint:t,nonce:o}=window.SiteTemplatePickerAdmin;e(document).ready((()=>{e("#template-site-id").select2({ajax:{url:t+"?_wpnonce="+o,dataType:"json",data:e=>({search:e.term,page:e.page||1})}})})),e(document).on("select2:open",(()=>{document.querySelector(".select2-search__field").focus()}));const a=document.querySelectorAll('#cboxol_template_categorychecklist input[type="checkbox"]');d(),a.forEach((e=>{e.addEventListener("change",d)}));const r=document.querySelectorAll('.template-visibility-radios input[type="radio"]'),c=()=>{r.forEach((e=>{const t=e.getAttribute("aria-controls"),o="yes"===e.value&&e.checked||"no"===e.value&&!e.checked,a=document.getElementById(t);a&&(a.style.display=o?"block":"none")}))};c(),r.forEach((e=>{e.addEventListener("change",c)}));const n=document.querySelectorAll('#template-visibility-suboptions-academic-unit input[type="checkbox"]'),l=[...n],s=e=>{const t=e.target,o=t.dataset.slug,a=t.checked,r=(e,t)=>{l.filter((t=>t.dataset.parent===e)).forEach((e=>{e.checked=t,r(e.dataset.slug,t)}))};r(o,a)};function d(){const{categoryMap:e,courseCreateMemberTypes:t,courseGroupTypeSlug:o,lang:r}=SiteTemplatePickerAdmin;if(!e)return;const c=Array.from(a).filter((e=>e.checked)).map((e=>e.value));let n=[];c.forEach((t=>{const o=e[t];o&&(n=n.concat(o))})),n=[...new Set(n)];const l=1===n.length&&n[0]===o,s=document.querySelectorAll("input.template-visibility-limit-to-member-types"),d=document.querySelector(".template-visibility-limit-to-member-types-message");if(l){const e=[...s].filter((e=>t.includes(e.value))).map((e=>e.labels[0].textContent)),o=new Intl.ListFormat(r,{style:"long",type:"conjunction"}).format(e);d.style.display="block",d.querySelector(".member-type-names").textContent=o,s.forEach((e=>{e.disabled=!t.includes(e.value)}))}else d.style.display="none",s.forEach((e=>{e.disabled=!1}))}n.forEach((e=>{e.addEventListener("click",s)})),document.addEventListener("DOMContentLoaded",(function(){const e=document.querySelectorAll(".wp-list-table tbody tr");let t=null;e.forEach((e=>{e.draggable=!0,e.addEventListener("dragstart",(e=>{t=e.target,"TR"!==t.tagName&&(t=t.closest("tr")),e.dataTransfer.effectAllowed="move",e.dataTransfer.setData("text/html",t),t.classList.add("cboxol-dragging")}),!1),e.addEventListener("dragover",(e=>{e.preventDefault(),e.dataTransfer.dropEffect="move";const t="TR"===e.target.tagName?e.target:e.target.closest("tr");t.classList.add("cboxol-dragover"),t.addEventListener("dragleave",(()=>{t.classList.remove("cboxol-dragover")}))}),!1),e.addEventListener("drop",(e=>{const a="TR"===e.target.tagName?e.target:e.target.closest("tr");t!==a&&(t.parentNode.insertBefore(t,a.nextSibling||a),function(){const e=document.querySelectorAll(".wp-list-table tbody tr"),t=Array.from(e).map(((e,t)=>({id:e.id.replace("post-",""),position:t+1}))),a=new URLSearchParams;a.append("order",JSON.stringify(t)),a.append("security",o),fetch(ajaxurl+"?action=cboxol_update_site_template_order",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:a}).then((e=>e.json())).then((()=>{})).catch((()=>{}))}(),t.classList.add("cboxol-just-dropped"),setTimeout((()=>{t.classList.remove("cboxol-just-dropped")}),1e3)),document.querySelectorAll("tr").forEach((e=>{e.classList.remove("cboxol-dragover"),e.classList.remove("cboxol-dragging")}))}),!1)}))}))}(jQuery)})();