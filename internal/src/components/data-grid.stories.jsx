import React from 'react'
import DataGrid from './data-grid'

export default {
	title: 'DataGrid',
	component: DataGrid,
	argTypes: {
		onSelect: {
			description:
				'callback for when a selection is made. arg is all the data from the selected index'
		},
		columns: {
			description: 'See react-table columns'
		},
		data: {
			description:
				'If null then the data is loading and the DataGrid will be in a loading state. Otherwise this is the data source of the items in the list.'
		}
	},
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => (
	<div style={{ width: '100%', height: '90vh' }}>
		<DataGrid {...args} />
	</div>
)

export const Loading = Template.bind({})
Loading.args = {
	idColumn: 'userID',
	columns: [
		{ accessor: 'userID', Header: 'User ID' },
		{ accessor: 'itemID', Header: 'Question ID' },
		{ accessor: 'score', Header: 'Score' }
	],
	data: null
}

export const NoData = Template.bind({})
NoData.args = {
	idColumn: 'userID',
	isLoading: false,
	columns: [
		{ accessor: 'userID', Header: 'User ID' },
		{ accessor: 'itemID', Header: 'Question ID' },
		{ accessor: 'score', Header: 'Score' }
	],
	data: []
}

const generateData = howMany => {
	const getRandomInt = max => Math.floor(Math.random() * Math.floor(max))
	const generateRow = index => ({
		userID: index * 100,
		itemID: getRandomInt(500),
		score: getRandomInt(100)
	})
	const data = []
	for (let i = 1; i < howMany; i++) {
		data.push(generateRow(i))
	}
	return data
}

export const Data = Template.bind({})
Data.args = {
	idColumn: 'userID',
	isLoading: false,
	columns: [
		{ accessor: 'userID', Header: 'User ID' },
		{ accessor: 'itemID', Header: 'Question ID' },
		{ accessor: 'score', Header: 'Score' }
	],
	data: generateData(500)
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
