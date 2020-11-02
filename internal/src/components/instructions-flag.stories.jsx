import React from 'react'

import InstructionsFlag from './instructions-flag'

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
	text: 'Here is some example text'
}
