import React from 'react'

import DataGridResponses from './data-grid-responses'

export default {
	component: DataGridResponses,
	title: 'DataGridResponses',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridResponses {...args} />

export const Example = Template.bind({})
Example.args = {
	responses: [
		{
			userName: 'Chris Lowe',
			response: 'A',
			score: 0,
			time: 1604067091
		},
		{
			userName: 'Chris Lowe',
			response: 'C',
			score: 100,
			time: 1604067108
		}
	]
}
