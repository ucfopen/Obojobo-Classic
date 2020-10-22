import React from 'react'

import DefList from './DefList'

export default {
	component: DefList,
	title: 'DefList',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <DefList {...args} />

export const Create = Template.bind({})
Create.args = {
	mode: 'create',
}

export const EditNonExternal = Template.bind({})
EditNonExternal.args = {
	mode: 'edit',
	isExternallyLinked: false,
}

export const EditExternal = Template.bind({})
EditExternal.args = {
	mode: 'edit',
	isExternallyLinked: true,
}
