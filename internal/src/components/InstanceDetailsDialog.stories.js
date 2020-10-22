import React from 'react'

import InstanceDetailsDialog from './InstanceDetailsDialog'

export default {
	component: InstanceDetailsDialog,
	title: 'InstanceDetailsDialog',
	argTypes: {
		isExternallyLinked: {
			description: 'If true, the instance is linked to an LMS and has no start and end date',
		},
		startDate: {
			description: 'If isExternallyLinked is true then this must be null',
		},
		endDate: {
			description: 'If isExternallyLinked is true then this must be null',
		},
	},
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <InstanceDetailsDialog {...args} />

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
