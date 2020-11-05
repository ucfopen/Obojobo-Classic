import './data-grid-assessment-scores.scss'

import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import DataGridStudentScoreCell from './data-grid-student-score-cell'
import PropTypes from 'prop-types'
import DataGridAttemptsCell from './data-grid-attempts-cell'

const getTimestampCell = ({ value }) => (
	<DataGridTimestampCell value={value} display="horizontal" showSeconds={true} />
)

const getStudentScoreCell = ({ value }) => <DataGridStudentScoreCell {...value} />

const DataGridAssessmentScores = ({
	data,
	selectedIndex,
	onSelect,
	onClickAddAdditionalAttempt,
	onClickRemoveAdditionalAttempt
}) => {
	const getDataGridAttemptsCell = ({ value, row }) => {
		return (
			<DataGridAttemptsCell
				{...value}
				onClickAddAdditionalAttempt={() => onClickAddAdditionalAttempt(row.index)}
				onClickRemoveAdditionalAttempt={() => onClickRemoveAdditionalAttempt(row.index)}
			/>
		)
	}

	return (
		<div className="repository--data-grid-assessment-scores">
			<DataGrid
				data={data}
				columns={[
					{ accessor: 'user', Header: 'User' },
					{ accessor: 'score', Header: 'Score', Cell: getStudentScoreCell },
					{ accessor: 'lastSubmitted', Header: 'Last Submitted', Cell: getTimestampCell },
					{
						accessor: 'attempts',
						Header: 'Attempts',
						Cell: getDataGridAttemptsCell
					}
				]}
				selectedIndex={selectedIndex}
				onSelect={onSelect}
			/>
		</div>
	)
}

DataGridAssessmentScores.propTypes = {
	data: PropTypes.arrayOf(
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
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired,
	onClickAddAdditionalAttempt: PropTypes.func.isRequired,
	onClickRemoveAdditionalAttempt: PropTypes.func.isRequired
}

export default DataGridAssessmentScores
