import React from 'react'

import HeaderBar from './HeaderBar'

export default {
	component: HeaderBar,
	title: 'HeaderBar',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <HeaderBar {...args} />

export const Example = Template.bind({})
Example.args = {
	userName: 'Scottie Pippen',
}
