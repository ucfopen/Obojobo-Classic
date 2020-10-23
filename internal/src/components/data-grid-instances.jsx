import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'
import PropTypes from 'prop-types';

const columns = [
	{accessor: 'name', Header: 'Title'},
	{accessor: 'courseID', Header: 'Course' },
	{accessor: 'startTime', Header: 'Start', Cell: DataGridTimestampCell},
	{accessor: 'endTime', Header: 'End' , Cell: DataGridTimestampCell}
]

const DataGridInstances = ({data, onSelect}) => <DataGrid data={data} columns={columns} onSelect={onSelect} />

DataGridInstances.propTypes = {
	data: PropTypes.oneOfType([null, PropTypes.arrayOf(PropTypes.shape({
		name: PropTypes.string.isRequired,
		courseID: PropTypes.string.isRequired,
		startTime: PropTypes.string.isRequired,
		endTime: PropTypes.string.isRequired
	}))]),
	onSelect: PropTypes.func.isRequired
}

export default DataGridInstances
