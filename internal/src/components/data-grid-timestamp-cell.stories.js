import React from 'react'
import DataGridTimestampCell from './data-grid-timestamp-cell'

export default {
	title: 'DataGridTimestampCell',
	component: DataGridTimestampCell,
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <DataGridTimestampCell {...args} />

export const Vertical = Template.bind({})
Vertical.args = {
	value: 1455050441,
	display: 'vertical',
	showSeconds: false,
}

export const Horizontal = Template.bind({})
Horizontal.args = {
	value: 1455050441,
	display: 'horizontal',
	showSeconds: true,
}

export const Blank = Template.bind({})
Blank.args = {
	value: null,
}
