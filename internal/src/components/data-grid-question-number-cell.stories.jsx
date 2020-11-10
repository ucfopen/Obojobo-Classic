import React from 'react'

import DataGridQuestionNumberCell from './data-grid-question-number-cell'

export default {
	component: DataGridQuestionNumberCell,
	title: 'DataGridQuestionNumberCell',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridQuestionNumberCell {...args} />

export const NonAlternate = Template.bind({})
NonAlternate.args = {
	displayNumber: 9,
	altNumber: 1,
	totalAlts: 1
}

export const Alternate = Template.bind({})
Alternate.args = {
	displayNumber: 2,
	altNumber: 1,
	totalAlts: 2
}
