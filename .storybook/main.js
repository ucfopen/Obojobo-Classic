const custom = require('../internal/src/webpack.config.js')(null, {})

module.exports = {
	"stories": [
		"../internal/src/**/*.stories.mdx",
		"../internal/src/**/*.stories.@(js|jsx|ts|tsx)"
	],
	"addons": [
		"@storybook/addon-links",
		"@storybook/addon-essentials"
	],
	webpackFinal: (config) => {
		return {
			...config,
			module: { ...config.module, rules: custom.module.rules },
			plugins: [...config.plugins, ...custom.plugins ]
		}
	}
}
