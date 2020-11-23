import './data-grid-student-score-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'
import Button from './button'

export default function DataGridStudentScoreCell({ value, isScoreImported, onClickScoreDetails }) {
	return (
		<div className="data-grid-student-score-cell">
			<span className="data-grid-student-score-cell--score">{value !== null ? value : '--'}</span>
			{isScoreImported ? (
				<span className="data-grid-student-score-cell--imported-text">(Imported)</span>
			) : (
				<Button type="text-bold" text="Details..." onClick={onClickScoreDetails} />
			)}
		</div>
	)
}

DataGridStudentScoreCell.defaultProps = {
	isScoreImported: false
}

DataGridStudentScoreCell.propTypes = {
	value: PropTypes.number,
	isScoreImported: PropTypes.bool.isRequired,
	onClickScoreDetails: PropTypes.func.isRequired
}
