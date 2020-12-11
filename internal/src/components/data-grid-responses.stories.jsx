import React from 'react'

import DataGridResponses from './data-grid-responses'

export default {
	title: 'DataGridResponses',
	component: DataGridResponses,
	argTypes: {
		onSelect: {
			description:
				'callback for when a selection is made. arg is all the data from the selected index'
		},
		columns: {
			description: 'See react-table columns'
		},
		data: {
			description:
				'If null then the data is loading and the DataGridResponses will be in a loading state. Otherwise this is the data source of the items in the list.'
		}
	},
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridResponses {...args} />

export const Loading = Template.bind({})
Loading.args = {
	data: null
}

export const NoData = Template.bind({})
NoData.args = {
	responses: []
}

export const Data = Template.bind({})
Data.args = {
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
