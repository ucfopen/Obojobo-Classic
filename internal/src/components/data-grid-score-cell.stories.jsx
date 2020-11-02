import React from 'react'

import DataGridScoreCell from './data-grid-score-cell'

export default {
	component: DataGridScoreCell,
	title: 'DataGridScoreCell',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridScoreCell {...args} />

export const Perc100 = Template.bind({})
Perc100.args = {
	value: 100
}

export const Perc50 = Template.bind({})
Perc50.args = {
	value: 50
}

export const Perc0 = Template.bind({})
Perc0.args = {
	value: 0
}

export const NotAnswered = Template.bind({})
NotAnswered.args = {
	value: null
}
