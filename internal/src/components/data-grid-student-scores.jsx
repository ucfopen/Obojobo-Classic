import './data-grid-student-scores.scss'

import React from 'react'
import PropTypes from 'prop-types'
import DataGrid from './data-grid'
import DataGridScoreCell from './data-grid-score-cell'
import DataGridQuestionBodyCell from './data-grid-question-body-cell'
import DataGridQuestionNumberCell from './data-grid-question-number-cell'

const getQuestionNumberCell = ({ value }) => {
	return <DataGridQuestionNumberCell {...value} />
}

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
	{ accessor: 'questionNumber', Header: 'Question #', Cell: getQuestionNumberCell },
	{ accessor: 'type', Header: 'Type', Cell: getType },
	{ accessor: 'questionItems', Header: 'Question Content', Cell: getQuestionBodyCell },
	{ accessor: 'score', Header: 'Score', Cell: DataGridScoreCell }
]

const DataGridStudentScores = ({ data, selectedIndex, onSelect }) => (
	<div className="repository--data-grid-student-scores">
		<DataGrid
			data={data}
			columns={columns}
			selectedIndex={selectedIndex}
			onSelect={onSelect}
			sortable={false}
		/>
	</div>
)

DataGridStudentScores.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			questionNumber: PropTypes.shape({
				displayNumber: PropTypes.number,
				altNumber: PropTypes.oneOfType([null, PropTypes.number])
			}),
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
			score: PropTypes.oneOfType([null, PropTypes.number])
		})
	),
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired
}

export default DataGridStudentScores
