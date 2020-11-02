import React from 'react'

import QuestionScoreDetails from './question-score-details'

export default {
	component: QuestionScoreDetails,
	title: 'QuestionScoreDetails',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <QuestionScoreDetails {...args} />

export const Example = Template.bind({})
Example.args = {
	question: {
		questionID: '19683',
		userID: 6661,
		itemType: 'MC',
		answers: [
			{
				answerID: '13223835175f9adeced6c001.08892275',
				userID: 0,
				answer:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Correct Answer</FONT></P></TEXTFORMAT>',
				weight: 100,
				feedback:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Correct Feedback</FONT></P></TEXTFORMAT>',
				_explicitType: 'obo\\lo\\Answer'
			},
			{
				answerID: '3168872425f9adeced6c065.10467592',
				userID: 0,
				answer:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect Answer</FONT></P></TEXTFORMAT>',
				weight: 0,
				feedback:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect Feedback</FONT></P></TEXTFORMAT>',
				_explicitType: 'obo\\lo\\Answer'
			}
		],
		perms: 0,
		items: [
			{
				pageItemID: 0,
				component: 'TextArea',
				data:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">MC Question with text</FONT></P></TEXTFORMAT>',
				media: [],
				advancedEdit: 0,
				options: null,
				_explicitType: 'obo\\lo\\PageItem'
			}
		],
		questionIndex: 0,
		feedback: { correct: '', incorrect: '' },
		createTime: 0,
		_explicitType: 'obo\\lo\\Question'
	},
	responses: [
		{
			userName: 'Chris Lowe',
			response: 'A',
			score: 100,
			time: 1604067091
		},
		{
			userName: 'Chris Lowe',
			response: 'B',
			score: 0,
			time: 1604067108
		}
	]
}
