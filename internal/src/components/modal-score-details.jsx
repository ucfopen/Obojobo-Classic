import './modal-score-details.scss'
import React, { useState } from 'react'
import PropTypes from 'prop-types'
import DataGridStudentScores from './data-grid-student-scores'
import QuestionPreview from './question-preview'
import InstructionsFlag from './instructions-flag'

export default function ModalScoreDetails(props) {
	const [previewType, setPreviewType] = useState(null)

	const renderPreview = () => {
		switch (previewType) {
			case 'question':
				return <QuestionPreview question={props.question} response={props.response} />
			default:
				return (
					<div className="instructions">
						<InstructionsFlag text="Select an attempt row to see details about that attempt" />
						<InstructionsFlag text="Select a question to see how the student answered" />
					</div>
				)
		}
	}

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
				<div className="student-scores">
					<DataGridStudentScores
						data={props.data}
						onSelect={() => {
							setPreviewType('question')
						}}
					/>
				</div>
			</div>
			<div className="right-pane">{renderPreview()}</div>
		</div>
	)
}

ModalScoreDetails.propTypes = {
	userID: PropTypes.string.isRequired,
	instID: PropTypes.string.isRequired,
	loID: PropTypes.string.isRequired
}
