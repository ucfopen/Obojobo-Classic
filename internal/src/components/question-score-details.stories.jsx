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

export const MCQuestion = Template.bind({})
MCQuestion.args = {
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

export const QAQuestion = Template.bind({})
QAQuestion.args = {
	question: {
		questionID: '19684',
		userID: 6661,
		itemType: 'QA',
		answers: [
			{
				answerID: '19833089495f9adeced7c489.27569956',
				userID: 0,
				answer: 'Correct Answer One',
				weight: 0,
				feedback: '',
				_explicitType: 'obo\\lo\\Answer'
			},
			{
				answerID: '17191905075f9adeced7c4d9.00871620',
				userID: 0,
				answer: 'Correct Answer Two',
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
						height: 519,
						width: 640,
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
		questionIndex: 0,
		feedback: {
			correct: '',
			incorrect:
				'<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#383838" LETTERSPACING="0" KERNING="0">Corrective Feedback</FONT></P></TEXTFORMAT>'
		},
		createTime: 0,
		_explicitType: 'obo\\lo\\Question'
	},
	responses: [
		{
			userName: 'Chris Lowe',
			response: 'test answer',
			score: 0,
			time: 1604067091
		},
		{
			userName: 'Chris Lowe',
			response: 'Correct Answer Two',
			score: 100,
			time: 1604067108
		}
	]
}

export const MediaQuestion = Template.bind({})
MediaQuestion.args = {
	question: {
		questionID: '19685',
		userID: 6661,
		itemType: 'Media',
		answers: [],
		perms: 0,
		items: [
			{
				pageItemID: 0,
				component: 'MediaView',
				data: '',
				media: [
					{
						mediaID: 6090,
						auth: '6661',
						title: 'Match the Law and Court Cases copy',
						itemType: 'kogneato',
						descText: '',
						createTime: 1533064081,
						copyright: 'Content from Materia.',
						thumb: '0',
						url: 'O2ZZA',
						size: '0',
						length: '0',
						perms: null,
						height: 0,
						width: 0,
						meta: {
							guest_access: false,
							$$hashKey: 'object:4',
							state: 'pending',
							created_at: '1533064020',
							name: 'Match the Law and Court Cases copy',
							id: 'O2ZZA',
							width: 0,
							is_embedded: false,
							clean_name: 'match-the-law-and-court-cases-copy',
							open_at: '-1',
							close_at: '-1',
							preview_url: 'https://materia.ucf.edu/preview/O2ZZA',
							student_access: false,
							embedded_only: false,
							widget: {
								package_hash: '0cae815d7d60ecb23a491f2479a9e316',
								in_catalog: '1',
								is_scalable: '0',
								question_types: '',
								dir: '14-matching/',
								is_answer_encrypted: '1',
								creator: 'creator.html',
								is_scorable: '1',
								meta_data: {
									supported_data: ['Question/Answer'],
									demo: 'NaOJs',
									about:
										'Matching provides a left and a right list. Students are asked to match the items on the left with the corresponding item on the right.',
									excerpt:
										'Students must match one set of words or phrases to a corresponding word, phrase, or definition.',
									features: ['Customizable', 'Scorable', 'Mobile Friendly', 'Media']
								},
								width: '750',
								is_playable: '1',
								clean_name: 'matching',
								group: 'Materia',
								api_version: '2',
								player: 'player.html',
								flash_version: '0',
								is_qset_encrypted: '1',
								name: 'Matching',
								id: '14',
								is_storage_enabled: '0',
								is_editable: '1',
								created_at: '1532011703',
								score_module: 'Matching',
								height: '548'
							},
							selected: true,
							attempts: '-1',
							is_draft: false,
							play_url: 'https://materia.ucf.edu/play/O2ZZA/match-the-law-and-court-cases-copy',
							height: 0,
							is_student_made: false,
							qset: { data: null, version: null },
							user_id: '110458',
							embed_url: 'https://materia.ucf.edu/embed/O2ZZA/match-the-law-and-court-cases-copy',
							img: 'https://static.materia.ucf.edu/widget/14-matching/img/icon-60.png',
							edit_url: 'https://materia.ucf.edu/my-widgets/#O2ZZA'
						},
						attribution: 0,
						_explicitType: 'obo\\lo\\Media'
					}
				],
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
			response: 0,
			score: 0,
			time: 1604067091
		},
		{
			userName: 'Chris Lowe',
			response: 100,
			score: 100,
			time: 1604067108
		}
	]
}
