import React from 'react'
import Button from './button'
import DefList from './def-list'
import PropTypes from 'prop-types'
import FlashHTML from './flash-html'
import SectionHeader from './section-header'
import './modal-about-lo.scss'

export default function ModalAboutLO(props) {
	const items = [
		{ label: 'Learn Time', value: props.learnTime.toString() },
		{ label: 'Language', value: props.language },
		{ label: 'Content', value: props.numContentPages.toString() },
		{ label: 'Practice', value: props.numPracticeQuestions.toString() },
		{ label: 'Assessment', value: props.numAssessmentQuestions.toString() },
		{ label: 'Author Notes', value: props.authorNotes }
	]

	return (
		<div className="modal-about-learning-object">
			<SectionHeader label={'About this learning object'} />

			<DefList className="def-list" items={items} />

			<SectionHeader label={'Learning Objective'} />

			<div className="flash-html-container">
				<FlashHTML value={props.learningObjective} />
			</div>

			<Button text="Close" type="text" onClick={props.onClose} />
		</div>
	)
}

ModalAboutLO.propTypes = {
	learnTime: PropTypes.number.isRequired,
	language: PropTypes.string.isRequired,
	numContentPages: PropTypes.number.isRequired,
	numPracticeQuestions: PropTypes.number.isRequired,
	numAssessmentQuestions: PropTypes.number.isRequired,
	authorNotes: PropTypes.string.isRequired,
	learningObjective: PropTypes.string.isRequired
}
