import React from 'react'
import DataGrid from './data-grid'
import DataGridTimestampCell from './data-grid-timestamp-cell'

const columns = [
	{accessor: 'name', Header: 'Title'},
	{accessor: 'courseID', Header: 'Course' },
	{accessor: 'startTime', Header: 'Start', Cell: DataGridTimestampCell},
	{accessor: 'endTime', Header: 'End' , Cell: DataGridTimestampCell}
]

const DataGridInstances = ({data, isLoading, onSelect}) => <DataGrid data={data} isLoading={isLoading} columns={columns} onSelect={onSelect} />

export default DataGridInstances
