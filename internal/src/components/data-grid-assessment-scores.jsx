import './data-grid-assessment-scores.scss'

import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import DataGridStudentScoreCell from './data-grid-student-score-cell'
import PropTypes from 'prop-types'
import DataGridAttemptsCell from './data-grid-attempts-cell'

const DataGridTimestampCellWithSeconds = props => (
	<DataGridTimestampCell {...props} display="horizontal" showSeconds={true} />
)

const DataGridAssessmentScores = ({
	data,
	rowCount,
	onClickSetAdditionalAttempt,
	onClickScoreDetails
}) => {

	const columns = React.useMemo(() => [
		{ accessor: 'user', Header: 'User' },
		{ accessor: 'score', Header: 'Score', Cell: DataGridStudentScoreCell, onClick: onClickScoreDetails},
		{ accessor: 'lastSubmitted', Header: 'Last Submitted', Cell: DataGridTimestampCellWithSeconds },
		{ accessor: 'numAttemptsTaken', Header: 'Attempts', Cell: DataGridAttemptsCell, onClick: onClickSetAdditionalAttempt }
	], [onClickSetAdditionalAttempt, onClickScoreDetails])

	const height = React.useMemo(() => {
		const rowHeight = 58
		const min = 3
		const max = 25
		return Math.min(Math.max(rowCount + 1, min), max) * rowHeight
	}, [data])

	return (
		<div className="repository--data-grid-assessment-scores" style={{height: `${height}px`}}>
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
			lastSubmitted: PropTypes.string,
			attempts: PropTypes.shape({
				numAttemptsTaken: PropTypes.number.isRequired,
				numAdditionalAttemptsAdded: PropTypes.number.isRequired,
				numAttempts: PropTypes.number.isRequired,
				isAttemptInProgress: PropTypes.bool
			})
		})
	),
	onClickSetAdditionalAttempt: PropTypes.func.isRequired,
	onClickScoreDetails: PropTypes.func.isRequired
}

export default DataGridAssessmentScores
