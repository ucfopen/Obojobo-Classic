import React from 'react'

import DataGridAttemptsCell from './data-grid-attempts-cell'

export default {
	component: DataGridAttemptsCell,
	title: 'DataGridAttemptsCell',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridAttemptsCell {...args} />

export const NoAdditionalAttempts = Template.bind({})
NoAdditionalAttempts.args = {
	numAttemptsTaken: 7,
	numAdditionalAttemptsAdded: 0,
	numAttempts: 10
}

export const AdditionalAttemptsApplied = Template.bind({})
AdditionalAttemptsApplied.args = {
	numAttemptsTaken: 7,
	numAdditionalAttemptsAdded: 5,
	numAttempts: 10
}

export const AttemptInProgress = Template.bind({})
AttemptInProgress.args = {
	numAttemptsTaken: 0,
	numAdditionalAttemptsAdded: 0,
	numAttempts: 2,
	isAttemptInProgress: true
}
