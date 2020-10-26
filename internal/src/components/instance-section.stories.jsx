import React from 'react'
import InstanceSection from './instance-section'

export default {
	title: 'InstanceSection',
	component: InstanceSection,
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <InstanceSection {...args} />

export const NothingSelected = Template.bind({})
NothingSelected.args = {
	instance: null
}

export const Example = Template.bind({})
Example.args = {
	instance: {
		instID: '1438',
		loID: '14427',
		userID: '6661',
		userName: 'Zachary A Berry',
		name: 'Conducting a Literature Review ',
		courseID: 'deleteme',
		createTime: '1282069407',
		startTime: '1282069380',
		endTime: '1282082400',
		attemptCount: '1',
		scoreMethod: 'h',
		allowScoreImport: '1',
		perms: [],
		courseData: { type: 'none' },
		externalLink: null,
		originalID: 0,
		_explicitType: 'obo\\lo\\InstanceData'
	}
}
