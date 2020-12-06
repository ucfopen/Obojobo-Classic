import React from 'react'
import DataGridInstances from './data-grid-instances'

export default {
	title: 'DataGridInstances',
	component: DataGridInstances,
	argTypes: {
		onSelect: {
			description:
				'callback for when a selection is made. arg is all the data from the selected index'
		},
		data: {
			description:
				'If null then the data is loading and the DataGridInstances will be in a loading state. Otherwise this is the data source of the items in the list.'
		}
	},
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridInstances {...args} />

export const Loading = Template.bind({})
Loading.args = {
	idColumn: 'instID',
	data: null
}

export const NoData = Template.bind({})
NoData.args = {
	idColumn: 'instID',
	data: []
}

export const Data = Template.bind({})
Data.args = {
	idColumn: 'instID',
	data: [
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
			_explicitType: 'obo\\lo\\InstanceData'
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
			_explicitType: 'obo\\lo\\InstanceData'
		}
	]
}

const now = Date.now() / 1000
const getRandomInt = max => Math.floor(Math.random() * Math.floor(max))
const generateRow = index => ({
	isSelected: false,
	instID: index + '',
	loID: getRandomInt(1500000) + '',
	userID: getRandomInt(5000) + '',
	userName: 'Zachary A Berry',
	name: 'Conducting a Literature Review For College Students ' + getRandomInt(600),
	courseID: 'the_id_of_a_course' + getRandomInt(600),
	createTime: getRandomInt(now) + '',
	startTime: getRandomInt(now) + '',
	endTime: getRandomInt(now) + '',
	attemptCount: '1',
	scoreMethod: 'h',
	allowScoreImport: '1',
	perms: {
		copy: 0,
		giveCopy: 0,
		giveGlobal: 0,
		givePublish: 0,
		giveRead: 0,
		giveWrite: 0,
		publish: 0,
		read: 0,
		userID: -1,
		write: 0,
		_explicitType: 'obo\\perms\\Permissions'
	},
	courseData: { type: 'none' },
	externalLink: null,
	originalID: 0,
	_explicitType: 'obo\\lo\\InstanceData'
})

const generateData = howMany => {
	const data = []
	const firstRow = generateRow(0)
	firstRow.name = '------------------- FIRST row --------------'
	data.push(firstRow)
	for (let i = 1; i < howMany; i++) {
		data.push(generateRow(i))
	}
	const lastRow = generateRow(howMany)
	lastRow.name = 'z ------------------- LAST row -------------- z'
	data.push(lastRow)
	return data
}

export const FiftyThousandRows = Template.bind({})
FiftyThousandRows.args = {
	data: generateData(50000)
}
