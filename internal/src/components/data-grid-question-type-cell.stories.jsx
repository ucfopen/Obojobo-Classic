import React from 'react'

import DataGridQuestionTypeCell from './data-grid-question-type-cell'

export default {
	component: DataGridQuestionTypeCell,
	title: 'DataGridQuestionTypeCell',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <DataGridQuestionTypeCell {...args} />

export const MultipleChoice = Template.bind({})
MultipleChoice.args = {
	value: 'MC'
}

export const ShortAnswer = Template.bind({})
ShortAnswer.args = {
	value: 'QA'
}

export const Media = Template.bind({})
Media.args = {
	value: 'Media'
}
