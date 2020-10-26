import React from 'react'

import SearchField from './search-field'

export default {
	component: SearchField,
	title: 'SearchField',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <SearchField {...args} />

export const Empty = Template.bind({})
Empty.args = {
	placeholder: 'Example placeholder'
}

export const NonEmpty = Template.bind({})
NonEmpty.args = {
	placeholder: 'Find a basketball player...',
	value: 'Scottie Pippen'
}
