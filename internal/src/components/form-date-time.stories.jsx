import React from 'react'

import FormDateTime from './form-date-time'

export default {
	component: FormDateTime,
	title: 'FormDateTime',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <FormDateTime {...args} />

export const Null = Template.bind({})
Null.args = {
	value: null
}

export const NonNull = Template.bind({})
NonNull.args = {
	value: 1455050437
}
