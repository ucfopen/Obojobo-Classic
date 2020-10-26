import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridAttemptsCell({
	numAttemptsTaken,
	numAdditionalAttemptsAdded,
	numAttempts,
	isAttemptInProgress,
	onClickRemoveAdditionalAttempt,
	onClickAddAdditionalAttempt,
}) {
	return <div>@TODO</div>
}

DataGridAttemptsCell.defaultProps = {
	isAttemptInProgress: false,
}

DataGridAttemptsCell.propTypes = {
	numAttemptsTaken: PropTypes.number.isRequired,
	numAdditionalAttemptsAdded: PropTypes.number.isRequired,
	numAttempts: PropTypes.number.isRequired,
	isAttemptInProgress: PropTypes.bool.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
}
