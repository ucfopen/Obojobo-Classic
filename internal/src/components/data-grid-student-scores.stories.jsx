import React from 'react'

import DataGridStudentScores from './data-grid-student-scores'

export default {
	component: DataGridStudentScores,
	title: 'DataGridStudentScores',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridStudentScores {...args} />

export const Example = Template.bind({})
Example.args = {
	data: [
		{
			questionNumber: 1,
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
			mean: 100
		},
		{
			questionNumber: '1b',
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
			mean: 50
		},
		{
			questionNumber: '2',
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
			mean: null
		}
	]
}
