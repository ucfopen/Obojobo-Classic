import React from 'react'
import PropTypes from 'prop-types'
import './data-grid-question-number-cell.scss'

export default function DataGridQuestionNumberCell(props) {
	const { questionNumber, altNumber, altTotal } = props.row.original

	return (
		<div className="data-grid-question-number-cell">
			<p className="question-info">Q{questionNumber}</p>
			{altTotal > 1 && altNumber > 1 ? <p>(Alt {String.fromCharCode(64 + altNumber)})</p> : null}
		</div>
	)
}

DataGridQuestionNumberCell.propTypes = {
	value: PropTypes.number,
	row: PropTypes.object
}
