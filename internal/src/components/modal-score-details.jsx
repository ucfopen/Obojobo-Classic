import './modal-score-details.scss'
import React from 'react'
import PropTypes from 'prop-types'
import DataGridStudentScores from './data-grid-student-scores'
import QuestionPreview from './question-preview'

export default function ModalScoreDetails() {
	return (
		<div className="modal-score-details">
			<div className="left-pane">
				<div className="modal-title">
					<h3>Student Score Details</h3>
					<p>Berry, Zach A</p>
				</div>
				<div className="sort-attempts">
					<span>Group by</span>
					<div className="radio-group">
						<input type="radio" id="attempts" name="group-by" />
						<label htmlFor="attempts">Attempts</label>
						<input type="radio" id="questions" name="group-by" />
						<label htmlFor="questions">Questions</label>
					</div>
				</div>
				<DataGridStudentScores />
			</div>
			<div className="right-pane">
				<QuestionPreview />
			</div>
		</div>
	)
}

ModalScoreDetails.propTypes = {
	userID: PropTypes.string.isRequired,
	instID: PropTypes.string.isRequired,
	loID: PropTypes.string.isRequired
}
