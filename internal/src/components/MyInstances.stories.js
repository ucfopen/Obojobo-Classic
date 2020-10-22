import React from 'react'

import MyInstances from './MyInstances'

export default {
	component: MyInstances,
	title: 'MyInstances',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <MyInstances {...args} />

export const Loading = Template.bind({})
Loading.args = {
	label: 'Details',
}

export const Loaded = Template.bind({})
Loaded.args = {
	label: 'Details',
}

export const SearchResults = Template.bind({})
SearchResults.args = {
	label: 'Details',
}

export const SortingByCourseName = Template.bind({})
SortingByCourseName.args = {
	label: 'Details',
}
