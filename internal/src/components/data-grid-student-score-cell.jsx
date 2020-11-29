import './data-grid-student-score-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'
import Button from './button'

export default function DataGridStudentScoreCell({ value, row, onClick }) {
	const click = React.useCallback(() => {onClick(row.original.user, row.original.userID)}, [row, onClick])
	return (
		<div className="data-grid-student-score-cell">
			<span className="data-grid-student-score-cell--score">{value ?? '--'}</span>
			{row.original.isScoreImported ? (
				<span className="data-grid-student-score-cell--imported-text">(Imported)</span>
			) : (
				<Button type="text-bold" text="Details..." onClick={click} />
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
