import React from 'react'
import DataGrid from './data-grid'

export default {
	title: 'DataGrid',
	component: DataGrid,
	argTypes: {
		onSelect: {
			description:
				'callback for when a selection is made. arg is all the data from the selected index',
		},
		columns: {
			description: 'See react-table columns'
		},
		data: {
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
	isLoading: true,
	columns: [
		{accessor: 'userID', Header: 'User ID'},
		{accessor: 'itemID', Header: 'Question ID' },
		{accessor: 'score', Header: 'Score'},
	],
	data: []
}

export const NoData = Template.bind({})
NoData.args = {
	isLoading: false,
	columns: [
		{accessor: 'userID', Header: 'User ID'},
		{accessor: 'itemID', Header: 'Question ID' },
		{accessor: 'score', Header: 'Score'},
	],
	data: []
}

export const Data = Template.bind({})
Data.args = {
	isLoading: false,
	columns: [
		{accessor: 'userID', Header: 'User ID'},
		{accessor: 'itemID', Header: 'Question ID' },
		{accessor: 'score', Header: 'Score'},
	],
	data: [
		{
			userID: 1,
			itemID: 1,
			score: 0
		},
		{
			userID: 2,
			itemID: 443,
			score: 99
		},
		{
			userID: 3,
			itemID: 13,
			score: 100
		},
		{
			userID: 2,
			itemID: 33434,
			score: 12
		}
	]
}

// export const Objects = Template.bind({})
// Objects.args = {
// 	type: 'object',
// 	selectedIndex: 0,
// 	items: [],
// }

// export const LibraryObjects = Template.bind({})
// LibraryObjects.args = {
// 	type: 'object',
// 	selectedIndex: 0,
// 	items: [],
// }

// export const Media = Template.bind({})
// Media.args = {
// 	type: 'object',
// 	selectedIndex: 0,
// 	items: [],
// }
