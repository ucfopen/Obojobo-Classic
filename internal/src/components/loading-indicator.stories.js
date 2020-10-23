import React from 'react'

import LoadingIndicator from './loading-indicator'

export default {
	component: LoadingIndicator,
	title: 'LoadingIndicator',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <LoadingIndicator {...args} />

export const Blank = Template.bind({})
Blank.args = {
	isLoading: false,
}

export const Loading = Template.bind({})
Loading.args = {
	isLoading: true,
}
