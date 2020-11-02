import './data-grid-attempts-cell.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridAttemptsCell(props) {
	const {
		numAttemptsTaken,
		numAttempts,
		numAdditionalAttemptsAdded,
		isAttemptInProgress,
		onClickAddAdditionalAttempt,
		onClickRemoveAdditionalAttempt
	} = props

	return (
		<div className="data-grid-attempts-cell">
			<p className="attempts">
				{numAttemptsTaken} of {numAttempts + numAdditionalAttemptsAdded}
				{isAttemptInProgress ? <small className="attempts--in-progress">In progress</small> : null}
			</p>
			<div className="controls">
				{numAdditionalAttemptsAdded > 0 ? (
					<button onClick={onClickRemoveAdditionalAttempt}>-</button>
				) : null}
				<button onClick={onClickAddAdditionalAttempt}>+</button>
			</div>
		</div>
	)
}

DataGridAttemptsCell.defaultProps = {
	isAttemptInProgress: false
}

DataGridAttemptsCell.propTypes = {
	numAttemptsTaken: PropTypes.number.isRequired,
	numAdditionalAttemptsAdded: PropTypes.number.isRequired,
	numAttempts: PropTypes.number.isRequired,
	isAttemptInProgress: PropTypes.bool.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired
}
