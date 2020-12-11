import React from 'react'

import ModalInstanceDetails from './modal-instance-details'

export default {
	component: ModalInstanceDetails,
	title: 'ModalInstanceDetails',
	argTypes: {
		isExternallyLinked: {
			description: 'If true, the instance is linked to an LMS and has no start and end date'
		},
		startDate: {
			description: 'If isExternallyLinked is true then this must be null'
		},
		endDate: {
			description: 'If isExternallyLinked is true then this must be null'
		}
	},
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <ModalInstanceDetails {...args} />

export const EditNonExternal = Template.bind({})
EditNonExternal.args = {
	isExternallyLinked: false,
	startDate: 1455050437,
	endDate: 1456050437
}

export const EditExternal = Template.bind({})
EditExternal.args = {
	mode: 'edit',
	isExternallyLinked: true
}
