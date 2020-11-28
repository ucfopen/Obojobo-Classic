import './data-grid-student-scores.scss'

import React from 'react'
import PropTypes from 'prop-types'
import DataGrid from './data-grid'
import DataGridScoreCell from './data-grid-score-cell'
import DataGridQuestionBodyCell from './data-grid-question-body-cell'
import DataGridQuestionNumberCell from './data-grid-question-number-cell'

const getType = ({ value }) => {
	switch (value) {
		case 'MC':
			return 'Multiple choice'

		case 'QA':
			return 'Fill in the blank'

		case 'Media':
			return 'Media'
	}

	return ''
}

const getQuestionBodyCell = ({ value }) => <DataGridQuestionBodyCell items={value} />

const columns = [
	{ accessor: 'attemptIndex', Header: 'Attempt', width: 70},
	{ accessor: 'questionNumber', Header: 'Question #', Cell: DataGridQuestionNumberCell, width: 90},
	// { accessor: 'type', Header: 'Type', Cell: getType },
	{ accessor: 'questionItems', Header: 'Question Content', Cell: getQuestionBodyCell },
	{ accessor: 'score', Header: 'Score', Cell: DataGridScoreCell, width: 55 }
]

const DataGridStudentScores = ({ data, selectedIndex, onSelect }) => (
	<div className="repository--data-grid-student-scores"  style={{ width: '100%', height: '100%' }}>
		<DataGrid
			// idColumn={'questionID'}
			data={data}
			columns={columns}
			selectedIndex={selectedIndex}
			onSelect={onSelect}
			sortable={true}
		/>
	</div>
)

DataGridStudentScores.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			questionNumber: PropTypes.number,
			altNumber: PropTypes.number,
			altTotal: PropTypes.number,
			type: PropTypes.oneOf(['MC', 'QA', 'Media']),
			questionItems: PropTypes.arrayOf(
				PropTypes.shape({
					component: PropTypes.oneOf(['TextArea', 'MediaView']),
					data: PropTypes.string,
					media: PropTypes.arrayOf(
						PropTypes.shape({
							title: PropTypes.string,
							itemType: PropTypes.oneOf(['pic', 'kogneato', 'swf', 'flv', 'mp3', 'youTube'])
						})
					)
				})
			),
			score: PropTypes.number
		})
	),
	selectedIndex: PropTypes.number,
	onSelect: PropTypes.func.isRequired
}

export default DataGridStudentScores
