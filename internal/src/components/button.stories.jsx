// YourComponent.stories.js

import React from 'react';
import Button from './button';

// This default export determines where your story goes in the story list
export default {
	title: 'Button',
	component: Button,
};

const Template = (args) => <Button {...args} />;

export const FirstStory = Template.bind({});

FirstStory.args = {
	text: 'Simple Button',
	onClick: () => {}
}
