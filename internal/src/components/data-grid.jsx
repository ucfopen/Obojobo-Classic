import React, {useState, useCallback} from 'react'
import {useTable} from 'react-table'
import DataGridTimestampCell from './data-grid-timestamp-cell'

const columns = [
	{accessor: 'name', Header: 'Title'},
	{accessor: 'courseID', Header: 'Course' },
	{accessor: 'startTime', Header: 'Start', Cell: DataGridTimestampCell},
	{accessor: 'endTime', Header: 'End' , Cell: DataGridTimestampCell}
]


const DataGrid = ({data}) => {
	const instanceTable = useTable({columns, data})
	const {
		getTableProps,
		getTableBodyProps,
		headerGroups,
		rows,
		prepareRow,
	  } = instanceTable

	return (
		<table {...getTableProps()} className={'repository-data-table'}>
			<thead>
				{headerGroups.map(headerGroup => (
					<tr {...headerGroup.getHeaderGroupProps()}>
						{headerGroup.headers.map(column => (
							<th {...column.getHeaderProps()}>
								{column.render('Header')}
							</th>
						))}
					</tr>
				))}
			</thead>
			<tbody {...getTableBodyProps()}>
				{rows.map(row => {
					prepareRow(row)
					return (
					<tr {...row.getRowProps()}>
						{row.cells.map(cell => {
							return (
								<td {...cell.getCellProps()}>
									{cell.render('Cell')}
								</td>
							)
						})}
					</tr>
					)
				})}
			</tbody>
		</table>
	)
}

export default DataGrid
