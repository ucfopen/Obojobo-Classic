import React from 'react'

import FlashHTML from './flash-html'

export default {
	component: FlashHTML,
	title: 'FlashHTML',
	parameters: {
		controls: {
			expanded: true,
		},
	},
}

const Template = (args) => <FlashHTML {...args} />

export const EmptyExample = Template.bind({})
EmptyExample.args = {
	value: '',
}

export const SimpleExample = Template.bind({})
SimpleExample.args = {
	value:
		'<TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="1">Example text</FONT></P></TEXTFORMAT>',
}

export const ComplexExample = Template.bind({})
ComplexExample.args = {
	value:
		'<TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="1">Given examples that include the following, students will be able to identify what constitutes plagiarism in their academic work and how to avoid the common causes of plagiarism when they use:<FONT KERNING="0"></FONT></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">direct quotes,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">paraphrased text,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">or summarized text.</FONT></LI></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT>',
}
