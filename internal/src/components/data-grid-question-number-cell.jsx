import React from 'react'
import PropTypes from 'prop-types'
import './data-grid-question-number-cell.scss'

export default function DataGridQuestionNumberCell(props) {
	const altInfo =
		props.totalAlts <= 1 ? null : (
			<p>
				Alternate {props.altNumber}/{props.totalAlts}
			</p>
		)

	return (
		<div className="data-grid-question-number-cell">
			<p className="question-info">Question {props.displayNumber}:</p>
			{altInfo}
		</div>
	)
}

DataGridQuestionNumberCell.propTypes = {
	displayNumber: PropTypes.number.isRequired,
	altNumber: PropTypes.number.isRequired,
	totalAlts: PropTypes.number.isRequired
}
