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

export const Example = Template.bind({})
Example.args = {
	attemptNumber: 2,
	score: 67,
	numAnsweredQuestions: 2,
	numTotalQuestions: 3,
	startTime: 1603982504,
	endTime: 1603982521
}
