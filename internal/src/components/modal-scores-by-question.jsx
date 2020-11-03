import React from 'react'
import PropTypes from 'prop-types'
import DataGridScoresByQuestions from './data-grid-scores-by-question'
import QuestionScoreDetails from './question-score-details'
import './modal-scores-by-question.scss'

export default function ModalScoresByQuestion() {
	return (
		<div className="modal-scores-by-question">
			<div className="scores-by-question--left-sidebar">
				<h2>Scores By Question</h2>
				<DataGridScoresByQuestions />
			</div>

			<div className="score-details-right-content">
				<QuestionScoreDetails />
			</div>
		</div>
	)
}

ModalScoresByQuestion.propTypes = {
	instID: PropTypes.string.isRequired,
	loID: PropTypes.string.isRequired
}
