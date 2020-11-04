import React from 'react'

import AttemptDetails from './attempt-details'

export default {
	component: AttemptDetails,
	title: 'AttemptDetails',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <AttemptDetails {...args} />

export const ShortDuration = Template.bind({})
ShortDuration.args = {
	attemptNumber: 2,
	score: 67,
	numAnsweredQuestions: 2,
	numTotalQuestions: 3,
	startTime: 1603982504,
	endTime: 1603982521
}

export const MediumDuration = Template.bind({})
MediumDuration.args = {
	attemptNumber: 4,
	score: 0,
	numAnsweredQuestions: 500,
	numTotalQuestions: 500,
	startTime: 1603982504,
	endTime: 1603993089
}

export const LongDuration = Template.bind({})
LongDuration.args = {
	attemptNumber: 99,
	score: 100,
	numAnsweredQuestions: 2,
	numTotalQuestions: 2,
	startTime: 1603982504,
	endTime: 1604993089
}
