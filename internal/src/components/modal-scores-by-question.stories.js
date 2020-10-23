import React from 'react'

import ModalScoresByQuestion from './modal-scores-by-question'

export default {
	component: ModalScoresByQuestion,
	title: 'ModalScoresByQuestion',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <ModalScoresByQuestion {...args} />

export const Example = Template.bind({})
Example.args = {}
