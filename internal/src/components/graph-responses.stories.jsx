import React from 'react'

import GraphResponses from './graph-responses'

export default {
	component: GraphResponses,
	title: 'GraphResponses',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <GraphResponses {...args} />

export const Example = Template.bind({})
Example.args = {
	data: [
		{
			label: 'A',
			amount: 0,
			isCorrect: false
		},
		{
			label: 'B',
			amount: 0,
			isCorrect: false
		},
		{
			label: 'C',
			amount: 5,
			isCorrect: false
		},
		{
			label: 'D',
			amount: 29,
			isCorrect: true
		}
	]
}
