<template>
	<div>
		<div class="cboxol-item-type-content-section">
			<on-off-switch v-bind:slug="data.slug"></on-off-switch>
		</div>

		<div class="cboxol-item-type-content-section">
			<label
				v-bind:for="data.slug + '-name'"
				class="cboxol-item-type-content-section-header"
			>{{ strings.itemTypeNameLabel }}</label>
			<input
				v-bind:placeholder="strings.addNewType"
				v-bind:id="data.slug + '-name'"
				v-model="name"
				v-on:change="setIsModified"
				v-bind:autofocus="! name"
			>
		</div>

		<div class="cboxol-item-type-content-section item-type-settings">
			<h3 class="cboxol-item-type-content-section-header">{{ strings.settings }}</h3>

			<div v-for="setting in data.settings">
				<component :is="setting.component" v-bind:slug="data.slug"></component>
			</div>
		</div>

		<div class="cboxol-item-type-content-section item-type-labels">
			<h3 class="cboxol-item-type-content-section-header">{{ strings.labels }}</h3>

			<div v-for="label in data.labels">
				<type-label v-bind:typeSlug="data.slug" v-bind:labelSlug="label.slug"></type-label>
			</div>
		</div>

		<div v-if="'group' === objectType" class="cboxol-item-type-content-section item-type-template">
			<h3 class="cboxol-item-type-content-section-header">{{ strings.template }}</h3>

			<p>{{ strings.templateSiteDescription }}</p>

			<div class="cboxol-template-site-links">
				<a v-bind:href="templateAdminUrl">{{ strings.templateDashboardLink }}</a> | <a v-bind:href="templateUrl">{{ strings.templateViewLink }}</a>
			</div>

		</div>
</template>
