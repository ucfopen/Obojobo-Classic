import React from 'react'
import InstanceSection from './instance-section'

export default {
	title: 'InstanceSection',
	component: InstanceSection,
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <InstanceSection {...args} />

export const NothingSelected = Template.bind({})
NothingSelected.args = {
	instance: null,
}

export const Example = Template.bind({})
Example.args = {
	instance: { todo: '@TODO' },
}
