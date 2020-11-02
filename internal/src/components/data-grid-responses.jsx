/* eslint-disable react/jsx-key */

import React from 'react'
import PropTypes from 'prop-types'
import DataGridTimestampCell from './data-grid-timestamp-cell'

import './data-grid.scss'

export default function DataGridResponses({ responses }) {
	return (
		<table className="repository--data-grid">
			<thead>
				<tr>
					<th>Student</th>
					<th>Response</th>
					<th>Score</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>
				{responses.map(response => (
					<tr>
						<td>{response.userName}</td>
						<td>{response.response}</td>
						<td>{response.score}</td>
						<td>
							<DataGridTimestampCell value={response.time} />
						</td>
					</tr>
				))}
			</tbody>
		</table>
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
	)
}
