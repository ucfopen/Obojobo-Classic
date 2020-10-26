import React from 'react'
import PropTypes from 'prop-types'

export default function ModalAboutLO({
	learnTime,
	language,
	numContentPages,
	numPracticeQuestions,
	numAssessmentQuestions,
	authorNotes,
	learningObjective,
}) {
	return <div>@TODO</div>
}

ModalAboutLO.propTypes = {
	learnTime: PropTypes.number.isRequired,
	language: PropTypes.string.isRequired,
	numContentPages: PropTypes.number.isRequired,
	numPracticeQuestions: PropTypes.number.isRequired,
	numAssessmentQuestions: PropTypes.number.isRequired,
	authorNotes: PropTypes.string.isRequired,
	learningObjective: PropTypes.string.isRequired,
}
