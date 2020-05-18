<template>
    <div class="onoffswitch">
        <input
			type="checkbox"
			ref="input"
			name="onoffswitch"
			class="onoffswitch-checkbox"
			v-bind:id="uniqueId"
			v-model="isEnabled"
		>
        <label class="onoffswitch-label" v-bind:for="uniqueId">
			<span class="screen-reader-text">{{ strings.onOffSwitchLabel }}</span>
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
</template>

<script>
	import EntityTools from '../mixins/EntityTools.js'
	import i18nTools from '../mixins/i18nTools.js'

	export default {
		data() {
			return {
				uniqueId: 'onoffswitch-' + this.slug
			}
		},

		computed: {
			isEnabled: {
				get () {
					return this.getEntityProp( 'isEnabled' )
				},
				set (value) {
					this.setEntityProp( 'isEnabled', value )
				}
			}
		},

		mixins: [
			EntityTools,
			i18nTools
		],

		props: [
			'entityType',
			'slug'
		],
	}
</script>

<style>
.onoffswitch {
    position: relative; width: 90px;
    -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
}
.onoffswitch-checkbox {
    display: none !important;
}
.onoffswitch-label {
    display: block; overflow: hidden; cursor: pointer;
    border: 2px solid #999999; border-radius: 20px;
}
.onoffswitch-inner {
    display: block; width: 200%; margin-left: -100%;
    transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner:before, .onoffswitch-inner:after {
    display: block; float: left; width: 50%; height: 30px; padding: 0; line-height: 30px;
    font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
    box-sizing: border-box;
}
.onoffswitch-inner:before {
    content: "ON";
    padding-left: 10px;
    background-color: #34A7C1; color: #FFFFFF;
}
.onoffswitch-inner:after {
    content: "OFF";
    padding-right: 10px;
    background-color: #EEEEEE; color: #999999;
    text-align: right;
}
.onoffswitch-switch {
    display: block; height: 18px; width: 18px; margin: 6px;
    background: #FFFFFF;
    position: absolute; top: 18; bottom: 0;
    right: 56px;
    border: 2px solid #999999; border-radius: 20px;
    transition: all 0.3s ease-in 0s;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
    margin-left: 0;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
    right: 0px;
}
</style>
