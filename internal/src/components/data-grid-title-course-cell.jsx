import './data-grid-title-course-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'

const DataTitleCourseCell = ({
	value: title,
	row: {
		original: { courseID }
	}
}) => {
	return (
		<div className="repository--title-course-cell" title={`${title} - ${courseID}`}>
			<span className="title">{title}</span>
			<span className="course">{courseID}</span>
		</div>
	)
}

DataTitleCourseCell.propTypes = {
	value: PropTypes.string
}

export default DataTitleCourseCell
