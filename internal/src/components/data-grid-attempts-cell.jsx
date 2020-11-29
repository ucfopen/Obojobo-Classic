import './data-grid-attempts-cell.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridAttemptsCell({value, row, column}) {
	const {attemptCount, additional, isAttemptInProgress, userID} = row.original
	const { onClick } = column
	const onAddClick = React.useCallback(() => {onClick(userID, additional+1)}, [onClick, userID, additional])
	const onRemoveClick = React.useCallback(() => {onClick(userID, additional-1)}, [onClick, userID, additional])
	return (
		<div className="data-grid-attempts-cell">
			<p className="attempts">
				{value} of {attemptCount + additional}
				{isAttemptInProgress ? <small className="attempts--in-progress">In progress</small> : null}
			</p>
			<div className="controls">
				{additional > 0 ? (
					<button onClick={onRemoveClick}>-</button>
				) : null}
				<button onClick={onAddClick}>+</button>
			</div>
		</div>
	)
}

DataGridAttemptsCell.defaultProps = {
	isAttemptInProgress: false
}

DataGridAttemptsCell.propTypes = {
	value: PropTypes.number.isRequired,
	row: PropTypes.shape({
		additional: PropTypes.number.isRequired,
		numAttempts: PropTypes.number.isRequired,
		isAttemptInProgress: PropTypes.bool.isRequired
	}),
	header: PropTypes.shape({
		onClick: PropTypes.func.isRequired
	})
}
