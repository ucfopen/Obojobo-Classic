import React from 'react'

import ModalScoresByQuestion from './modal-scores-by-question'

export default {
	component: ModalScoresByQuestion,
	title: 'ModalScoresByQuestion',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <ModalScoresByQuestion {...args} />

export const Example = Template.bind({})
Example.args = {
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
	submitQuestionLogsByUser: [
		{
			userName: 'Example, John Q.',
			logs: [
				{
					trackingID: '32',
					itemType: 'SubmitQuestion',
					createTime: '1605717512',
					loID: '7',
					visitID: '3',
					valueA: '8',
					valueB: '5370639865fb54ddf5d2346.31587389',
					valueC: '4',
					score: 100,
					page: 4,
					answerIndex: 2
				},

				{
					trackingID: '34',
					itemType: 'SubmitQuestion',
					createTime: '1605717514',
					loID: '7',
					visitID: '3',
					valueA: '7',
					valueB: '15194973475fb54ddf5cd272.93374547',
					valueC: '4',
					score: 100,
					page: 3,
					answerIndex: 2
				},

				{
					trackingID: '40',
					itemType: 'SubmitQuestion',
					createTime: '1605717523',
					loID: '7',
					visitID: '3',
					valueA: '8',
					valueB: '3460367205fb54ddf5d2257.82234754',
					valueC: '4',
					score: 0,
					page: 4,
					answerIndex: 1
				},

				{
					trackingID: '44',
					itemType: 'SubmitQuestion',
					createTime: '1605717529',
					loID: '7',
					visitID: '3',
					valueA: '7',
					valueB: '15194973475fb54ddf5cd272.93374547',
					valueC: '4',
					score: 100,
					page: 3,
					answerIndex: 2
				},

				{
					trackingID: '46',
					itemType: 'SubmitQuestion',
					createTime: '1605717531',
					loID: '7',
					visitID: '3',
					valueA: '8',
					valueB: '5370639865fb54ddf5d2346.31587389',
					valueC: '4',
					score: 100,
					page: 4,
					answerIndex: 2
				},

				{
					trackingID: '56',
					itemType: 'SubmitQuestion',
					createTime: '1605717553',
					loID: '7',
					visitID: '4',
					valueA: '8',
					valueB: '5370639865fb54ddf5d2346.31587389',
					valueC: '4',
					score: 100,
					page: 4,
					answerIndex: 2
				},

				{
					trackingID: '58',
					itemType: 'SubmitQuestion',
					createTime: '1605717555',
					loID: '7',
					visitID: '4',
					valueA: '5',
					valueB: '1392844035fb54ddf5a85b6.08770266',
					valueC: '4',
					score: 100,
					page: 1,
					answerIndex: 2
				},

				{
					trackingID: '62',
					itemType: 'SubmitQuestion',
					createTime: '1605717559',
					loID: '7',
					visitID: '4',
					valueA: '8',
					valueB: '5370639865fb54ddf5d2346.31587389',
					valueC: '4',
					score: 100,
					page: 4,
					answerIndex: 2
				},

				{
					trackingID: '64',
					itemType: 'SubmitQuestion',
					createTime: '1605717567',
					loID: '7',
					visitID: '4',
					valueA: '6',
					valueB: 'my response',
					valueC: '4',
					score: 0,
					page: 2,
					answerIndex: '?'
				}
			]
		}
	]
}
