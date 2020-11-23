import React from 'react'
import DataGrid from './data-grid'
import DataGridDateCell from './data-grid-date-cell'
import TitleCourseCell from './data-grid-title-course-cell'
import PropTypes from 'prop-types'

const columns = [
	{ accessor: 'name', Header: 'Title & Course', Cell: TitleCourseCell, width: 320 },
	{ accessor: 'startTime', Header: 'Start', Cell: DataGridDateCell, width: 40 },
	{ accessor: 'endTime', Header: 'End', Cell: DataGridDateCell, width: 40 }
]

const DataGridInstances = ({ data, selectedIndex, onSelect }) => (
	<div className="repository--data-grid-instances" style={{ width: '400px', height: '90vh' }}>
		<DataGrid
			idColumn="instID"
			data={data}
			columns={columns}
			selectedIndex={selectedIndex}
			onSelect={onSelect}
		/>
	</div>
)

DataGridInstances.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			name: PropTypes.string.isRequired,
			courseID: PropTypes.string.isRequired,
			startTime: PropTypes.string.isRequired,
			endTime: PropTypes.string.isRequired
		})
	),
	selectedIndex: PropTypes.number,
	onSelect: PropTypes.func.isRequired
}

export default DataGridInstances
