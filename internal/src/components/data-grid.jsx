/* eslint-disable react/jsx-key */

import React from 'react'
import { useTable } from 'react-table'
import PropTypes from 'prop-types'

import './data-grid.scss'

const DataGrid = ({ data, columns, selectedIndex, onSelect }) => {
	const isLoading = data === null

	// setup react-table
	const instanceTable = useTable({
		columns,
		data: data || []
	})

	const { getTableProps, getTableBodyProps, headerGroups, rows, prepareRow } = instanceTable

	return (
		<table {...getTableProps()} className={`repository--data-grid ${onSelect ? 'selectable' : ''}`}>
			<thead>
				{headerGroups.map(headerGroup => (
					<tr {...headerGroup.getHeaderGroupProps()}>
						{headerGroup.headers.map(column => (
							<th {...column.getHeaderProps()}>{column.render('Header')}</th>
						))}
					</tr>
				))}
			</thead>
			<tbody {...getTableBodyProps()}>
				{isLoading || !rows.length ? (
					<tr>
						<td className="no-data" colSpan={columns.length}>
							{isLoading ? 'loading...' : 'no data'}
						</td>
					</tr>
				) : (
					rows.map(row => {
						prepareRow(row)

						const className = row.index === selectedIndex ? 'selected' : ''
						const onClick = () => {
							if (!onSelect) return
							onSelect(row.index)
						}
						return (
							<tr {...row.getRowProps()} onClick={onClick} className={className}>
								{row.cells.map(cell => (
									<td {...cell.getCellProps()}>{cell.render('Cell')}</td>
								))}
							</tr>
						)
					})
				)}
			</tbody>
		</table>
	)
}

DataGrid.defaultProps = {
	data: null,
	columns: []
}

DataGrid.propTypes = {
	data: PropTypes.oneOfType([null, PropTypes.arrayOf(PropTypes.object)]),
	columns: PropTypes.arrayOf(PropTypes.object),
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]),
	onSelect: PropTypes.func.isRequired
}

export default DataGrid
