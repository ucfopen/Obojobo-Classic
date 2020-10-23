import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import DataGridStudentScoreCell from './data-grid-student-score-cell'
import PropTypes from 'prop-types'
import DataGridAttemptsCell from './data-grid-attempts-cell'

const getTimestampCell = ({ value }) => (
	<DataGridTimestampCell value={value} display="horizontal" showSeconds={true} />
)

const columns = [
	{ accessor: 'user', Header: 'User' },
	{ accessor: 'score', Header: 'Score', Cell: DataGridStudentScoreCell },
	{ accessor: 'lastSubmitted', Header: 'Last Submitted', Cell: getTimestampCell },
	{ accessor: 'attempts', Header: 'Attempts', Cell: DataGridAttemptsCell }
]

const DataGridAssessmentScores = ({ data }) => <DataGrid data={data} columns={columns} />

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
	)
}

export default DataGridAssessmentScores
