import React from 'react'
import PropTypes from 'prop-types'

export default function AssessmentScoresSection({ assessmentScores, selectedStudentIndex, onSelect, onClickRefresh }) {
	return <div>@TODO</div>
}

MyInstances.propTypes = {
	assessmentScores: PropTypes.arrayOf(
		PropTypes.shape({
			user: PropTypes.string.isRequired,
			score: PropTypes.shape({
				value: PropTypes.oneOfType([null, PropTypes.number]),
				isScoreImported: PropTypes.bool
			}).isRequired,
			lastSubmitted: PropTypes.oneOfType([null, PropTypes.string]).isRequired,
			attempts: PropTypes.shape({
				numAttemptsTaken: PropTypes.number.isRequired,
				numAdditionalAttemptsAdded: PropTypes.number.isRequired,
				numAttempts: PropTypes.number.isRequired,
				isAttemptInProgress: PropTypes.bool
			})
		})
	),
	selectedStudentIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired,
	onClickRefresh: PropTypes.func.isRequired
}