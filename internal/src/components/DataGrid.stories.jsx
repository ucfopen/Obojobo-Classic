import React from 'react'

import DataGrid from './DataGrid'

export default {
	component: DataGrid,
	title: 'DataGrid',
	argTypes: {
		onSelectIndex: {
			action: 'onSelectIndex',
			description:
				'When an item in the list is selected this method should be called with the index of the selected item',
		},
		type: {
			description: 'The type of data contained in "items"',
		},
		selectedIndex: {
			description:
				'If null then nothing is selected, otherwise this is the position of the selected item',
		},
		items: {
			description:
				'If null then the data is loading and the DataGrid will be in a loading state. Otherwise this is the data source of the items in the list.',
		},
	},
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <DataGrid {...args} />

export const Loading = Template.bind({})
Loading.args = {
	loading: true,
}

export const Instance = Template.bind({})
Instance.args = {
	type: 'instance',
	selectedIndex: null,
	items: [
		{
			instID: '1438',
			loID: '14427',
			userID: '6661',
			userName: 'Zachary A Berry',
			name: 'Conducting a Literature Review ',
			courseID: 'deleteme',
			createTime: '1282069407',
			startTime: '1282069380',
			endTime: '1282082400',
			attemptCount: '1',
			scoreMethod: 'h',
			allowScoreImport: '1',
			perms: [],
			courseData: { type: 'none' },
			externalLink: null,
			originalID: 0,
			_explicitType: 'obo\\lo\\InstanceData',
		},
		{
			instID: '5205',
			loID: '23735',
			userID: '6661',
			userName: 'Zachary A Berry',
			name: 'Citing Sources Using MLA Style',
			courseID: 'be_creative_lst',
			createTime: '1371668609',
			startTime: '0',
			endTime: '0',
			attemptCount: '1',
			scoreMethod: 'h',
			allowScoreImport: '1',
			perms: [],
			courseData: { type: 'none' },
			externalLink: 'canvas',
			originalID: 0,
			_explicitType: 'obo\\lo\\InstanceData',
		},
	],
}

export const Objects = Template.bind({})
Objects.args = {
	type: 'object',
	selectedIndex: 0,
	items: [],
}

export const LibraryObjects = Template.bind({})
LibraryObjects.args = {
	type: 'object',
	selectedIndex: 0,
	items: [],
}

export const Media = Template.bind({})
Media.args = {
	type: 'object',
	selectedIndex: 0,
	items: [],
}
