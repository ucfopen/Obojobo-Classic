/* eslint-disable react/jsx-key */

import React from 'react'
import PropTypes from 'prop-types'
import { DataGridWithInternalState } from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'

const columns = [
	{ accessor: 'userName', Header: 'Student' },
	{ accessor: 'response', Header: 'Response' },
	{ accessor: 'score', Header: 'Score' },
	{ accessor: 'createTime', Header: 'Time', Cell: DataGridTimestampCell }
]

export default function DataGridResponses({ responses, selectedIndex, onSelect }) {
	return (
		<DataGridWithInternalState
			data={responses}
			columns={columns}
			selectedIndex={selectedIndex}
			onSelect={onSelect}
		/>
	)
}

DataGridResponses.propTypes = {
	responses: PropTypes.arrayOf(
		PropTypes.shape({
			userName: PropTypes.string,
			response: PropTypes.string,
			score: PropTypes.number,
			time: PropTypes.number
		})
	),
	selectedIndex: PropTypes.number
}
