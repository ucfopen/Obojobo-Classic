import React from 'react'

import DefList from './def-list'

export default {
	component: DefList,
	title: 'DefList',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DefList {...args} />

export const Example = Template.bind({})
Example.args = {
	items: [
		{
			label: 'Favorite Color',
			value: 'Red'
		},
		{
			label: 'Pancakes or Waffles?',
			value: 'Waffles'
		},
		{
			value: 'This line has no label!'
		}
	]
}
