import React from 'react'

import ModalScoreDetails from './modal-score-details'

export default {
	component: ModalScoreDetails,
	title: 'ModalScoreDetails',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <ModalScoreDetails {...args} />

export const Example = Template.bind({})
Example.args = {
	userName: 'Example, John Q.',
	questions: [
		{
			questionID: '5',
			userID: 1,
			itemType: 'MC',
			answers: [
				{
					answerID: '16288766825fb54ddf5a8499.61727163',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect choice</FONT></P></TEXTFORMAT>',
					weight: 0,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				},
				{
					answerID: '1392844035fb54ddf5a85b6.08770266',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Correct choice</FONT></P></TEXTFORMAT>',
					weight: 100,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				}
			],
			perms: 0,
			items: [
				{
					pageItemID: 0,
					component: 'TextArea',
					data:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">Example MC Question</FONT></P></TEXTFORMAT>',
					media: [],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			questionIndex: 1,
			feedback: { correct: '', incorrect: '' },
			_explicitType: 'obo\\lo\\Question'
		},
		{
			questionID: '6',
			userID: 1,
			itemType: 'QA',
			answers: [
				{
					answerID: '7065301805fb54ddf5c9934.36102607',
					userID: 0,
					answer: 'Example correct answer',
					weight: 0,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				},
				{
					answerID: '8859340545fb54ddf5c9a42.08885525',
					userID: 0,
					answer: 'Example correct answer 2',
					weight: 0,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				}
			],
			perms: 0,
			items: [
				{
					pageItemID: 0,
					component: 'TextArea',
					data:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">Example short answer question</FONT></P></TEXTFORMAT>',
					media: [],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			questionIndex: 1,
			feedback: { correct: '', incorrect: '' },
			_explicitType: 'obo\\lo\\Question'
		},
		{
			questionID: '7',
			userID: 1,
			itemType: 'MC',
			answers: [
				{
					answerID: '11006114235fb54ddf5cd183.98347312',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect choice</FONT></P></TEXTFORMAT>',
					weight: 0,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				},
				{
					answerID: '15194973475fb54ddf5cd272.93374547',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Correct choice</FONT></P></TEXTFORMAT>',
					weight: 100,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				}
			],
			perms: 0,
			items: [
				{
					pageItemID: 0,
					component: 'TextArea',
					data:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">Example MC Question 2</FONT></P></TEXTFORMAT>',
					media: [],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			questionIndex: 1,
			feedback: { correct: '', incorrect: '' },
			_explicitType: 'obo\\lo\\Question'
		},
		{
			questionID: '8',
			userID: 1,
			itemType: 'MC',
			answers: [
				{
					answerID: '3460367205fb54ddf5d2257.82234754',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect choice</FONT></P></TEXTFORMAT>',
					weight: 0,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				},
				{
					answerID: '5370639865fb54ddf5d2346.31587389',
					userID: 0,
					answer:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Correct choice</FONT></P></TEXTFORMAT>',
					weight: 100,
					feedback: '',
					_explicitType: 'obo\\lo\\Answer'
				}
			],
			perms: 0,
			items: [
				{
					pageItemID: 0,
					component: 'TextArea',
					data:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">Example MC Question 3</FONT></P></TEXTFORMAT>',
					media: [],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			questionIndex: 0,
			feedback: { correct: '', incorrect: '' },
			_explicitType: 'obo\\lo\\Question'
		}
	],
	attemptLogs: [
		{
			attempt: {
				attemptID: '4',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '3',
				score: '100',
				unalteredScore: null,
				startTime: '1605717509',
				endTime: '1605717515',
				qOrder: '8,7',
				linkedAttemptID: '0'
			},
			scores: [
				{
					itemType: 'q',
					itemID: '7',
					answerID: '15194973475',
					answer: '15194973475fb54ddf5cd272.93374547',
					score: '100'
				},
				{
					itemType: 'q',
					itemID: '8',
					answerID: '5370639865',
					answer: '5370639865fb54ddf5d2346.31587389',
					score: '100'
				}
			]
		},
		{
			attempt: {
				attemptID: '5',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '3',
				score: '0',
				unalteredScore: null,
				startTime: '1605717517',
				endTime: '1605717525',
				qOrder: '7,8',
				linkedAttemptID: '0'
			},
			scores: [
				{
					itemType: 'q',
					itemID: '8',
					answerID: '3460367205',
					answer: '3460367205fb54ddf5d2257.82234754',
					score: '0'
				}
			]
		},
		{
			attempt: {
				attemptID: '6',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '3',
				score: '100',
				unalteredScore: null,
				startTime: '1605717527',
				endTime: '1605717532',
				qOrder: '7,8',
				linkedAttemptID: '0'
			},
			scores: [
				{
					itemType: 'q',
					itemID: '7',
					answerID: '15194973475',
					answer: '15194973475fb54ddf5cd272.93374547',
					score: '100'
				},
				{
					itemType: 'q',
					itemID: '8',
					answerID: '5370639865',
					answer: '5370639865fb54ddf5d2346.31587389',
					score: '100'
				}
			]
		},
		{
			attempt: {
				attemptID: '7',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '4',
				score: '0',
				unalteredScore: null,
				startTime: '1605717546',
				endTime: '1605717549',
				qOrder: '8,7',
				linkedAttemptID: '0'
			},
			scores: []
		},
		{
			attempt: {
				attemptID: '8',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '4',
				score: '100',
				unalteredScore: null,
				startTime: '1605717551',
				endTime: '1605717556',
				qOrder: '8,5',
				linkedAttemptID: '0'
			},
			scores: [
				{
					itemType: 'q',
					itemID: '5',
					answerID: '1392844035',
					answer: '1392844035fb54ddf5a85b6.08770266',
					score: '100'
				},
				{
					itemType: 'q',
					itemID: '8',
					answerID: '5370639865',
					answer: '5370639865fb54ddf5d2346.31587389',
					score: '100'
				}
			]
		},
		{
			attempt: {
				attemptID: '9',
				userID: '1',
				instID: '4',
				loID: '7',
				qGroupID: '4',
				visitID: '4',
				score: '50',
				unalteredScore: null,
				startTime: '1605717558',
				endTime: '1605717570',
				qOrder: '8,6',
				linkedAttemptID: '0'
			},
			scores: [
				{ itemType: 'q', itemID: '6', answerID: '0', answer: 'my response', score: '0' },
				{
					itemType: 'q',
					itemID: '8',
					answerID: '5370639865',
					answer: '5370639865fb54ddf5d2346.31587389',
					score: '100'
				}
			]
		}
	]
}
