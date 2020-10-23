import React from 'react'
import DataGridAssessmentScores from './data-grid-assessment-scores'

export default {
	title: 'DataGridAssessmentScores',
	component: DataGridAssessmentScores,
	argTypes: {
		data: {
			description:
				'If null then the data is loading and the DataGridAssessmentScores will be in a loading state. Otherwise this is the data source of the items in the list.'
		}
	},
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridAssessmentScores {...args} />

export const Loading = Template.bind({})
Loading.args = {
	data: null
}

export const NoData = Template.bind({})
NoData.args = {
	data: []
}

export const Data = Template.bind({})
Data.args = {
	data: [
		{
			user: 'Berry, Zachary A.',
			score: {
				value: 37,
				isScoreImported: false
			},
			lastSubmitted: '1455050441',
			attempts: {
				numAttemptsTaken: 3,
				numAdditionalAttemptsAdded: 3,
				numAttempts: 5,
				isAttemptInProgress: false
			}
		},
		{
			user: 'Scottie, Pippen',
			score: {
				value: null,
				isScoreImported: false
			},
			lastSubmitted: null,
			attempts: {
				numAttemptsTaken: 0,
				numAdditionalAttemptsAdded: 0,
				numAttempts: 5,
				isAttemptInProgress: true
			}
		},
		{
			user: 'Star, Ringo',
			score: {
				value: 64,
				isScoreImported: false
			},
			lastSubmitted: '1455050441',
			attempts: {
				numAttemptsTaken: 4,
				numAdditionalAttemptsAdded: 0,
				numAttempts: 5,
				isAttemptInProgress: true
			}
		},
		{
			user: 'Tennant, Neil',
			score: {
				value: 100,
				isScoreImported: true
			},
			lastSubmitted: '1455050441',
			attempts: {
				numAttemptsTaken: 1,
				numAdditionalAttemptsAdded: 0,
				numAttempts: 5,
				isAttemptInProgress: false
			}
		}
	]
}
