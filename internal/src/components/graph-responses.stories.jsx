import React from 'react'

import GraphResponses from './graph-responses'

export default {
	component: GraphResponses,
	title: 'GraphResponses',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => (
	<div style={{border: '1px solid #ccc', display: 'inline-block'}}>
		<GraphResponses {...args} />
	</div>
)

export const Example = Template.bind({})
Example.args = {
	data: [
		{
			label: 'A',
			value: 2,
			isCorrect: false
		},
		{
			label: 'B',
			value: 0,
			isCorrect: false
		},
		{
			label: 'C',
			value: 5,
			isCorrect: false
		},
		{
			label: 'D',
			value: 2,
			isCorrect: true
		}
	],
	height: 300,
	width: 300
}

export const Empty = Template.bind({})
Empty.args = {
	data: [	],
	height: 300,
	width: 300
}

export const Big = Template.bind({})
Big.args = {
	data: [
		{
			label: 'A',
			value: 2,
			isCorrect: false
		},
		{
			label: 'B',
			value: 200,
			isCorrect: false
		},
		{
			label: 'C',
			value: 20,
			isCorrect: false
		},
		{
			label: 'D',
			value: 10,
			isCorrect: true
		},
		{
			label: 'E',
			value: 35,
			isCorrect: true
		},
		{
			label: 'F',
			value: 2,
			isCorrect: true
		},
		{
			label: 'G',
			value: 15,
			isCorrect: true
		}
	],
	height: 500,
	width: 500
}

export const Small = Template.bind({})
Small.args = {
	data: [
		{
			label: 'A',
			value: 2,
			isCorrect: false
		},
		{
			label: 'B',
			value: 1,
			isCorrect: true
		},
	],
	height: 200,
	width: 200
}
