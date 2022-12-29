!function(){"use strict";function e(e){let t="";const n=Object.entries(e);let a;for(;a=n.shift();){let[e,s]=a;if(Array.isArray(s)||s&&s.constructor===Object){const t=Object.entries(s).reverse();for(const[a,s]of t)n.unshift([`${e}[${a}]`,s])}else void 0!==s&&(null===s&&(s=""),t+="&"+[e,s].map(encodeURIComponent).join("="))}return t.substr(1)}const{endpoint:t,perPage:n,categoryMap:a}=window.SiteTemplatePicker,s=window.CBOXOL_Group_Create.new_group_type;var i;const o=document.querySelector("#site-template-categories"),r=document.querySelector(".site-template-picker"),l=document.querySelector(".panel-template-picker"),c=document.querySelector(".site-template-pagination"),d=document.querySelector('[name="source_blog"]'),p=document.querySelector("#set-up-site-toggle"),u=document.querySelectorAll('[name="new_or_old"]'),m=window.SiteTemplatePicker.messages,g=window.SiteTemplatePicker.defaultMap,v=(null===(i=window.CBOXOL_Group_Create)||void 0===i?void 0:i.new_group_type)||null,h=v&&g.hasOwnProperty(v)?g[v]:0,f=h?h.toString():d.value;function _(i,o){r.innerHTML=`<p>${m.loading}</p>`,async function(i){let o,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:1;if(i)o=i;else for(var l in o=[0],a)-1!==a[l].indexOf(s)&&o.push(l);const c=e({_fields:["id","title","excerpt","featured_media","template_category","site_id","image","categories"],template_category:o,order:"desc",per_page:Number(n),page:r}),d=await fetch(t+"?"+c),p=await d.json();if(!d.ok)throw new Error(p.message);const u=Number(d.headers.get("X-WP-TotalPages"));return{templates:p.map((e=>({id:e.id,title:e.title.rendered,excerpt:e.excerpt.rendered,image:e.image,categories:e.categories,siteId:e.site_id}))),prev:r>1?r-1:null,next:u>r?r+1:null}}(i,o).then((e=>{let{templates:t,prev:n,next:a}=e;if(!t.length)return void(r.innerHTML=`<p>${m.noResults}</p>`);const s=t.map((e=>function(e){let{id:t,siteId:n,title:a,excerpt:s,image:i,categories:o}=e;return`\n\t<button type="button" class="site-template-component" data-template-id="${t}" data-template-site-id="${n}">\n\t\t<div class="site-template-component__image">\n\t\t\t${i?`<img src="${i}" alt="${a}">`:'<svg fill="currentColor" width="24" height="24" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>'}\n\t\t\t<div class="site-template-component__description">${s}</div>\n\t\t</div>\n\t\t<div class="site-template-component__meta">\n\t\t\t<span class="site-template-component__category">${o.join(", ")}</span>\n\t\t\t<div class="site-template-component__name">${a}</div>\n\t\t</div>\n\t</button>\n\t`}(e))).join("");r.innerHTML=s,L(f),function(e,t){const n=c.querySelector(".prev"),a=c.querySelector(".next"),s=c.classList.contains("hidden");e||t||s||c.classList.add("hidden"),n.disabled=!0,a.disabled=!0,e&&(n.dataset.page=e,n.disabled=!1),t&&(a.dataset.page=t,a.disabled=!1)}(n,a)}))}function w(){let e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];e?l.classList.remove("hidden"):(l.classList.add("hidden"),L(f))}function L(e){r.querySelectorAll(".site-template-component").forEach((t=>{t.dataset.templateId===e?(t.classList.add("is-selected"),d.value=t.dataset.templateSiteId):t.classList.remove("is-selected")}))}o.addEventListener("change",(function(e){const t="0"!==e.target.value?e.target.value:null;r.innerHTML=`<p>${m.loading}</p>`,_(t)})),r.addEventListener("click",(function(e){const t=e.target.closest(".site-template-component");t&&L(t.dataset.templateId)})),r.addEventListener("mouseover",(function(e){const t=e.target.closest(".site-template-component");t&&(t.classList.contains("has-hover")||t.classList.add("has-hover"))})),r.addEventListener("mouseout",(function(e){const t=e.target.closest(".site-template-component");t&&t.classList.contains("has-hover")&&t.classList.remove("has-hover")})),c.addEventListener("click",(function(e){const t=e.target.closest(".btn");t&&_("0"!==o.value?o.value:null,t.dataset.page?Number(t.dataset.page):null)})),u.forEach((e=>{e.addEventListener("change",(e=>w("new"===e.target.value)))})),p?(p.addEventListener("change",(e=>w(e.target.checked))),p.checked&&w(l.checked)):w(!0),_()}();