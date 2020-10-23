import React from 'react'

import ModalScoreDetails from './modal-score-details'

export default {
	component: ModalScoreDetails,
	title: 'ModalScoreDetails',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <ModalScoreDetails {...args} />

export const Example = Template.bind({})
Example.args = {}
