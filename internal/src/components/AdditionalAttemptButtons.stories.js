import React from 'react'

import AdditionalAttemptButtons from './AdditionalAttemptButtons'

export default {
	component: AdditionalAttemptButtons,
	title: 'AdditionalAttemptButtons',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <AdditionalAttemptButtons {...args} />

export const DecreaseDisabled = Template.bind({})
DecreaseDisabled.args = {
	isDecreaseEnabled: false,
}

export const DecreaseEnabled = Template.bind({})
DecreaseEnabled.args = {
	isDecreaseEnabled: true,
}
