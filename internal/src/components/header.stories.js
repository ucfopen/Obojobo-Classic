import React from 'react'

import Header from './header'

export default {
	component: Header,
	title: 'Header',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <Header {...args} />

export const Example = Template.bind({})
Example.args = {
	userName: 'Scottie Pippen',
}
