import React from 'react'

import DataGridStudentScoreCell from './data-grid-attempts-cell'

export default {
	component: DataGridStudentScoreCell,
	title: 'DataGridStudentScoreCell',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridStudentScoreCell {...args} />

export const InProgressScore = Template.bind({})
InProgressScore.args = {
	value: null,
	isScoreImported: false
}

export const Score = Template.bind({})
Score.args = {
	value: 77,
	isScoreImported: false
}

export const ImportedScore = Template.bind({})
ImportedScore.args = {
	value: 100,
	isScoreImported: true
}
