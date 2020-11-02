import React from 'react'

import HelpButton from './help-button'

export default {
	component: HelpButton,
	title: 'HelpButton',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <HelpButton {...args} />

export const Example = Template.bind({})
Example.args = {
	children: (
		<p>
			Here is some <b>example help</b>!
		</p>
	)
}
