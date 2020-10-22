import React from 'react'

import SearchField from './SearchField'

export default {
	component: SearchField,
	title: 'SearchField',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <SearchField {...args} />

export const Empty = Template.bind({})
Empty.args = {}

export const NonEmpty = Template.bind({})
NonEmpty.args = {
	value: 'Scottie Pippen',
}
