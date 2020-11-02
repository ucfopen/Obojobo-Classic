import React from 'react'

import AssessmentScoresSection from './assessment-scores-section'

export default {
	component: AssessmentScoresSection,
	title: 'AssessmentScoresSection',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <AssessmentScoresSection {...args} />

export const Example = Template.bind({})
Example.args = {
	assessmentScores: [
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
