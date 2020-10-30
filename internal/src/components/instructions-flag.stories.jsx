import React from 'react'

import InstructionsFlag from './instruction-flag'

export default {
	component: InstructionsFlag,
	title: 'InstructionsFlag',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <InstructionsFlag {...args} />

export const Example = Template.bind({})
Example.args = {
	label: 'This is an example'
}
