import React from 'react'

import AssessmentScoresGraph from './assessment-scores-summary'

export default {
	component: AssessmentScoresGraph,
	title: 'AssessmentScoresGraph',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <AssessmentScoresGraph {...args} />

export const Example = Template.bind({})
Example.args = {
	scores: [0, 0, 10, 100, 60, 88, 100, 80, 100, 100, 100, 100, 0],
}

// export const Example = Template.bind({})
// Example.args = {
// 	scoringMethod: 'average',
// 	scores: [
// 		{
// 			userID: '6661',
// 			user: {
// 				first: 'Zachary',
// 				last: 'Berry',
// 				mi: 'A',
// 			},
// 			additional: '1',
// 			attempts: [
// 				{
// 					attemptID: '1061098',
// 					score: '10',
// 					linkedAttempt: '0',
// 					submitted: true,
// 					submitDate: '1455050437',
// 				},
// 				{
// 					attemptID: '1061099',
// 					score: '0',
// 					linkedAttempt: '0',
// 					submitted: true,
// 					submitDate: '1455050441',
// 				},
// 				{
// 					attemptID: '1338474',
// 					score: '100',
// 					linkedAttempt: '0',
// 					submitted: true,
// 					submitDate: '1510763694',
// 				},
// 			],
// 			synced: false,
// 			syncedScore: 0,
// 		},
// 	],
// }
