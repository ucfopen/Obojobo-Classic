import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import PropTypes from 'prop-types'

const columns = [
	{ accessor: 'name', Header: 'Title' },
	{ accessor: 'courseID', Header: 'Course' },
	{ accessor: 'startTime', Header: 'Start', Cell: DataGridTimestampCell },
	{ accessor: 'endTime', Header: 'End', Cell: DataGridTimestampCell }
]

const DataGridInstances = ({ data, selectedIndex, onSelect }) => (
	<div style={{width: '90vw', height: '90vh', overflow: 'scroll'}}>
		<div className="repository--data-grid-instances">
			<DataGrid data={data} columns={columns} selectedIndex={selectedIndex} onSelect={onSelect} />
		</div>
	</div>
)

DataGridInstances.propTypes = {
	data: PropTypes.oneOfType([
		null,
		PropTypes.arrayOf(
			PropTypes.shape({
				name: PropTypes.string.isRequired,
				courseID: PropTypes.string.isRequired,
				startTime: PropTypes.string.isRequired,
				endTime: PropTypes.string.isRequired
			})
		)
	]),
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired
}

export default DataGridInstances
