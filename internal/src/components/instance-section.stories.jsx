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

export const NonCanvasExample = Template.bind({})
NonCanvasExample.args = {
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

export const CanvasExample = Template.bind({})
CanvasExample.args = {
	instance: {
		instID: '9999',
		loID: '14427',
		userID: '6661',
		userName: 'Zachary A Berry',
		name: 'Citing Sources using APA',
		courseID: 'ENC 1101',
		createTime: '1282069407',
		startTime: '0',
		endTime: '0',
		attemptCount: '99',
		scoreMethod: 'm',
		allowScoreImport: '0',
		perms: [],
		courseData: { type: 'none' },
		externalLink: 'Example external link',
		originalID: 0,
		_explicitType: 'obo\\lo\\InstanceData'
	}
}

export const StartDateInTheFuture = Template.bind({})
StartDateInTheFuture.args = {
	instance: {
		instID: '1438',
		loID: '14427',
		userID: '6661',
		userName: 'Zachary A Berry',
		name: 'Conducting a Literature Review ',
		courseID: 'deleteme',
		createTime: '1282069407',
		startTime: '1735689600',
		endTime: '1735889600',
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

export const InstanceOngoing = Template.bind({})
InstanceOngoing.args = {
	instance: {
		instID: '1438',
		loID: '14427',
		userID: '6661',
		userName: 'Zachary A Berry',
		name: 'Conducting a Literature Review ',
		courseID: 'deleteme',
		createTime: '1282069407',
		startTime: '1282069380',
		endTime: '1735689600',
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
