import React from 'react'

import RefreshButton from './RefreshButton'

export default {
	component: RefreshButton,
	title: 'RefreshButton',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <RefreshButton {...args} />

export const Example = Template.bind({})
Example.args = {
	label: 'Details',
}
