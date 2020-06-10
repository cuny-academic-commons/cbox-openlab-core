module.exports = {
	computed: {
		code: {
			get() {
				return this.$store.state.signupCodes[ this.wpPostId ].code
			},
			set( value ) {
				this.$store.commit(
					'setSignupCodeProperty',
					{
						wpPostId: this.wpPostId,
						field: 'code',
						value
					}
				)
			},
		},

		group: {
			get() {
				return this.$store.state.signupCodes[ this.wpPostId ].group
			},
			set( value ) {
				this.$store.commit(
					'setSignupCodeProperty',
					{
						wpPostId: this.wpPostId,
						field: 'group',
						value: value
					}
				)
			},
		},

		groupSlug() {
			return this.group.slug
		},

		id() {
			return 'signupCode-' + this.wpPostId
		},

		isLoading: {
			get() {
				return this.$store.state.isLoading.hasOwnProperty( this.id )
			},

			set( value ) {
				this.$store.commit( 'setIsLoading', { key: this.id, value } )
			}
		},

		memberType() {
			return this.$store.state.signupCodes[ this.wpPostId ].memberType
		},

		memberTypeSlug: {
			get() {
				return this.memberType.slug
			},
			set( value ) {
				this.$store.commit(
					'setSignupCodeProperty',
					{
						wpPostId: this.wpPostId,
						field: 'memberTypeSlug',
						value: value
					}
				)
			},
		},

	}
}
