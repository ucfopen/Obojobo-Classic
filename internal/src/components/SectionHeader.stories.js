import React from 'react'

import SectionHeader from './SectionHeader'

export default {
	component: SectionHeader,
	title: 'SectionHeader',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <SectionHeader {...args} />

export const Example = Template.bind({})
Example.args = {
	label: 'Details',
}
