// YourComponent.stories.js

import React from 'react';
import Button from './button';

// This default export determines where your story goes in the story list
export default {
	title: 'Button',
	component: Button
};

const Template = (args) => <Button {...args} />;

export const TextButton = Template.bind({});
TextButton.args = {
	text: 'Button',
	type: 'text'
}

export const SmallButton = Template.bind({});
SmallButton.args = {
	text: 'Button',
	type: 'small'
}

export const LargeButton = Template.bind({});
LargeButton.args = {
	text: 'Button',
	type: 'large'
}

export const AltButton = Template.bind({});
AltButton.args = {
	text: 'Button',
	type: 'alt'
}