<template>
	<tr>
		<th scope="row" class="check-column">
			<label class="screen-reader-text" :v-for="academicUnit.type + '-' + slug + '-cb'">{{ checkboxLabel }}</label>
			<input type="checkbox" :v-id="academicUnit.type + '-' + slug + '-cb'" :value="academicUnit.id" />
		</th>

		<td class="name column-name has-row-actions column-primary">
			<strong>{{ academicUnit.name }}</strong>
			<br />
			<div class="row-actions">
				<span class="edit"><a href="#" v-on:click="onEditClick">{{ strings.edit }}</a> | </span>
				<span class="delete"><a href="#" class="delete-tag" v-on:click="onDeleteClick">{{ strings.delete }}</a></span>
			</div>
		</td>

		<td class="parent column-parent">
			{{ parentName }}
		</td>

		<td class="posts column-posts">
			{{ academicUnit.count }}
		</td>
	</tr>
</template>

<script>
	import i18nTools from '../../mixins/i18nTools.js'

	export default {
		computed: {
			academicUnit() {
				return this.$store.state.academicUnits[ this.slug ]
			},

			checkboxLabel() {
				return this.strings.selectUnit.replace( '%s', this.academicUnit.name )
			},

			parentName() {
				const parentSlug = this.academicUnit.parent
				let name = ''

				if ( this.$store.state.academicUnits.hasOwnProperty( parentSlug ) ) {
					name = this.$store.state.academicUnits[ parentSlug ].name
				}

				return name
			}
		},

		methods: {
			onDeleteClick() {
				console.log('ok')
			},

			onEditClick() {
				console.log('ok')
			}
		},

		mixins: [
			i18nTools
		],

		props: {
			slug: {
				type: String,
				required: true
			}
		}
	}
</script>
