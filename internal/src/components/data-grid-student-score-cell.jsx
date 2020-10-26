import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridStudentScoreCell() {
	return <div>@TODO</div>
}

DataGridStudentScoreCell.defaultProps = {
	isScoreImported: false
}

DataGridStudentScoreCell.propTypes = {
	value: PropTypes.oneOfType([null, PropTypes.number]).isRequired,
	isScoreImported: PropTypes.bool.isRequired
}
