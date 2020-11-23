import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import PropTypes from 'prop-types'

const dateW = 77 // width of DataGridTimeStampCell
const columns = [
	{ accessor: 'name', Header: 'Title', width: 200 },
	{ accessor: 'courseID', Header: 'Course', width: 100 },
	{ accessor: 'endTime', Header: 'End', Cell: DataGridTimestampCell, width: dateW }
]

const DataGridInstances = ({ data, selectedIndex, onSelect }) => (
	<div className="repository--data-grid-instances" style={{width: '100%', height: '90vh'}}>
		<DataGrid idColumn='instID' data={data} columns={columns} selectedIndex={selectedIndex} onSelect={onSelect} />
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
