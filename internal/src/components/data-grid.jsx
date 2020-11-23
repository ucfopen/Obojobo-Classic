/* eslint-disable react/jsx-key */

import React from 'react'
import { useTable, useSortBy, useFlexLayout} from 'react-table'
import PropTypes from 'prop-types'
import CaretUp from '../../../assets/images/viewer/caret-up.svg'
import CaretDown from '../../../assets/images/viewer/caret-down.svg'
import { FixedSizeList } from 'react-window'
import AutoSizer from 'react-virtualized-auto-sizer'

import './data-grid.scss'
import LoadingIndicator from './loading-indicator'

const DataGrid = ({ data, columns, sortable, idColumn, onSelect}) => {
	const isLoading = data === null

	const getRowId = React.useCallback(row => row[idColumn], [])

	const defaultColumn = React.useMemo(
		() => ({
		  // When using the useFlexLayout:
		  minWidth: 30, // minWidth is only used as a limit for resizing
		  width: 150, // width is used for both the flex-basis and flex-grow
		  maxWidth: 200, // maxWidth is only used as a limit for resizing
		}),
		[]
	)

	// setup react-table
	const instanceTable = useTable(
		{
			defaultColumn,
			columns,
			data: data || [],
			getRowId
		},
		useSortBy,
		useFlexLayout
	)

	const [selectedId, setSelectedId] = React.useState(null)

	const { getTableProps, getTableBodyProps, headerGroups, rows, prepareRow } = instanceTable

	const RenderRow = React.useCallback(
		({index, style}) => {
			const row = rows[index]
			prepareRow(row)

			const selectedClass = row.id === selectedId ? 'selected' : ''
			const evenOddClass = index % 2 ? 'odd' : ''
			const onClick = () => {
				setSelectedId(row.id)
				if (onSelect) onSelect(row.original)
			}
			return (
				<div {...row.getRowProps({style})} onClick={onClick} className={`row ${selectedClass} ${evenOddClass}`}>
					{row.cells.map(cell => (
						<div {...cell.getCellProps()}>{cell.render('Cell')}</div>
					))}
				</div>
			)
		}
	)

	return (
		<div className={`repository--data-grid ${onSelect ? 'selectable' : ''}`} {...getTableProps()}>
			<div className="data-grid-head">
				{headerGroups.map(headerGroup => (
					<div className="row" {...headerGroup.getHeaderGroupProps()}>
						{headerGroup.headers.map(column => (
							<div
								{...column.getHeaderProps(
									sortable && !isLoading ? column.getSortByToggleProps() : {}
								)}
							>
								{column.render('Header')}
								{column.isSorted && column.isSortedDesc ? <CaretUp /> : null}
								{column.isSorted && !column.isSortedDesc ? <CaretDown /> : null}
							</div>
						))}
					</div>
				))}
			</div>
			<div className="data-grid-body" {...getTableBodyProps()}>
				{isLoading || !rows.length ? (
					<div className="no-data">
						{isLoading ? <LoadingIndicator isLoading={true} /> : 'No data'}
					</div>
				) : (
					<AutoSizer>
						{({ height, width}) => (
							<FixedSizeList
								height={height}
								itemCount={rows.length}
								itemSize={58}
								width={width}
							>
								{RenderRow}
							</FixedSizeList>
						)}
					</AutoSizer>
				)}

			</div>
		</div>
	)
}

DataGrid.defaultProps = {
	data: null,
	columns: [],
	sortable: true
}

DataGrid.propTypes = {
	data: PropTypes.oneOfType([null, PropTypes.arrayOf(PropTypes.object)]),
	columns: PropTypes.arrayOf(PropTypes.object),
	sortable: PropTypes.bool,
	selectedRow: PropTypes.oneOfType([null, PropTypes.arrayOf(PropTypes.object)]),
	onSelect: PropTypes.func.isRequired,
	idColumn: PropTypes.string
}

export default DataGrid
