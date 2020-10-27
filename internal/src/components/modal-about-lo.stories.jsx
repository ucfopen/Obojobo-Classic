import React from 'react'

import ModalAboutLO from './modal-about-lo'

export default {
	component: ModalAboutLO,
	title: 'ModalAboutLO',
	parameters: {
		controls: {
			expanded: true
		}
	}
}

const Template = args => <ModalAboutLO {...args} />

export const Example = Template.bind({})
Example.args = {
	learnTime: 20,
	language: 'English',
	numContentPages: 11,
	numPracticeQuestions: 13,
	numAssessmentQuestions: 10,
	authorNotes:
		'\rStudents will be able to identify the causes of plagiarism and how to avoid plagiarism.',
	learningObjective:
		'<TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="1">Given examples that include the following, students will be able to identify what constitutes plagiarism in their academic work and how to avoid the common causes of plagiarism when they use:<FONT KERNING="0"></FONT></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">direct quotes,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">paraphrased text,</FONT></LI></TEXTFORMAT><TEXTFORMAT LEFTMARGIN="10" RIGHTMARGIN="15" LEADING="3"><LI><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0">or summarized text.</FONT></LI></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT><TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Arial" SIZE="14" COLOR="#393939" LETTERSPACING="0" KERNING="0"></FONT></P></TEXTFORMAT>'
}
