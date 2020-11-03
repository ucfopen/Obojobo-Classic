import React from 'react'
import PropTypes from 'prop-types'
import './data-grid-question-number-cell.scss'

export default function DataGridQuestionNumberCell(props) {
	const altInfo = props.altNumber ? <p>Alternate {props.altNumber}/2</p> : null

	return (
		<div className="data-grid-question-number-cell">
			<p className="question-info">Question {props.displayNumber}:</p>
			{altInfo}
		</div>
	)
}

DataGridQuestionNumberCell.defaultProps = {
	altNumber: null
}

DataGridQuestionNumberCell.propTypes = {
	displayNumber: PropTypes.number.isRequired,
	altNumber: PropTypes.oneOfType([null, PropTypes.number])
}
