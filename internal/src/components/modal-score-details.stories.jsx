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
	data: [
		{
			questionNumber: {
				displayNumber: 1,
				altNumber: 1,
				totalAlts: 2
			},
			questionAlternateNumber: 1,
			type: 'MC',
			questionItems: [
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
			score: 100
		},
		{
			questionNumber: {
				displayNumber: 1,
				altNumber: 2,
				totalAlts: 2
			},
			type: 'QA',
			questionItems: [
				{
					pageItemID: 0,
					component: 'TextArea',
					data:
						'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">SA question, split</FONT></P></TEXTFORMAT>',
					media: [],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				},
				{
					pageItemID: 0,
					component: 'MediaView',
					data: '',
					media: [
						{
							mediaID: 3341,
							auth: '6661',
							title: 'The Pride',
							itemType: 'pic',
							descText: 'Photo from Flickr 2',
							createTime: 1344543906,
							copyright:
								'Photo used under Creative Commons from <U><A HREF="event:http://www.flickr.com/photos/79723524@N03/7745511508">AbdillahAbi</A></U>',
							thumb: '0',
							url: 'The_Pride.jpg',
							size: '174927',
							length: '0',
							perms: null,
							height: 417,
							width: 514.8,
							meta: 0,
							attribution: 1,
							_explicitType: 'obo\\lo\\Media'
						}
					],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			score: 50
		},
		{
			questionNumber: {
				displayNumber: 2,
				altNumber: 1,
				totalAlts: 1
			},
			type: 'Media',
			questionItems: [
				{
					pageItemID: 0,
					component: 'MediaView',
					data: '',
					media: [
						{
							mediaID: 3341,
							auth: '6661',
							title: 'The Pride',
							itemType: 'pic',
							descText: 'Photo from Flickr 2',
							createTime: 1344543906,
							copyright:
								'Photo used under Creative Commons from <U><A HREF="event:http://www.flickr.com/photos/79723524@N03/7745511508">AbdillahAbi</A></U>',
							thumb: '0',
							url: 'The_Pride.jpg',
							size: '174927',
							length: '0',
							perms: null,
							height: 417,
							width: 514.8,
							meta: 0,
							attribution: 1,
							_explicitType: 'obo\\lo\\Media'
						}
					],
					advancedEdit: 0,
					options: null,
					_explicitType: 'obo\\lo\\PageItem'
				}
			],
			score: null
		},
		{
			questionNumber: {
				displayNumber: 3,
				altNumber: 1,
				totalAlts: 1
			},
			type: 'MC',
			questionItems: [
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
			score: 0
		}
	],
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
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Partially Correct Answer</FONT></P></TEXTFORMAT>',
				weight: 50,
				feedback:
					'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#313131" LETTERSPACING="0" KERNING="0">Incorrect Feedback</FONT></P></TEXTFORMAT>',
				_explicitType: 'obo\\lo\\Answer'
			},
			{
				answerID: '3168872425f9adeced6c065.10467593',
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
	response: '13223835175f9adeced6c001.08892275'
}
