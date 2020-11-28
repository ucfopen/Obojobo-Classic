import './data-grid-assessment-scores.scss'

import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import DataGridStudentScoreCell from './data-grid-student-score-cell'
import PropTypes from 'prop-types'
import DataGridAttemptsCell from './data-grid-attempts-cell'

const DataGridAssessmentScores = ({
	data,
	onClickAddAdditionalAttempt,
	onClickRemoveAdditionalAttempt,
	onClickScoreDetails
}) => {

	const getTimestampCell = React.useMemo(() => ({ value }) => (
		<DataGridTimestampCell value={value} display="horizontal" showSeconds={true} />
	), [])

	const getDataGridAttemptsCell = React.useMemo(() => ({ value, row }) => {
		return (
			<DataGridAttemptsCell
				{...value}

				onClickAddAdditionalAttempt={() => {
					onClickAddAdditionalAttempt(
						row.original.userID,
						row.original.attempts.numAdditionalAttemptsAdded
					)
				}}
				onClickRemoveAdditionalAttempt={() =>
					onClickRemoveAdditionalAttempt(
						row.original.userID,
						row.original.attempts.numAdditionalAttemptsAdded
					)
				}
			/>
		)
	}, [onClickAddAdditionalAttempt, onClickRemoveAdditionalAttempt])

	const getStudentScoreCell = React.useMemo(() => ({ value, row }) => (
		<DataGridStudentScoreCell
			{...value}
			onClickScoreDetails={() => onClickScoreDetails(row.original.user, row.original.userID)}
		/>
	), [onClickScoreDetails])

	const columns = React.useMemo(() => [
		{ accessor: 'user', Header: 'User' },
		{ accessor: 'score', Header: 'Score', Cell: getStudentScoreCell },
		{ accessor: 'lastSubmitted', Header: 'Last Submitted', Cell: getTimestampCell },
		{ accessor: 'attempts', Header: 'Attempts', Cell: getDataGridAttemptsCell }
	], [getStudentScoreCell, getDataGridAttemptsCell])

	return (
		<div className="repository--data-grid-assessment-scores">
			<DataGrid
				data={data}
				idColumn='userID'
				columns={columns}
			/>
		</div>
	)
}

DataGridAssessmentScores.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			user: PropTypes.string.isRequired,
			userID: PropTypes.string.isRequired,
			score: PropTypes.shape({
				value: PropTypes.number,
				isScoreImported: PropTypes.bool
			}).isRequired,
			lastSubmitted: PropTypes.number,
			attempts: PropTypes.shape({
				numAttemptsTaken: PropTypes.number.isRequired,
				numAdditionalAttemptsAdded: PropTypes.number.isRequired,
				numAttempts: PropTypes.number.isRequired,
				isAttemptInProgress: PropTypes.bool
			})
		})
	),
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired,
	onClickScoreDetails: PropTypes.func.isRequired
}

export default DataGridAssessmentScores
