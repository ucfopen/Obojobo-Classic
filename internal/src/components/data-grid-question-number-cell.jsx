import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridQuestionNumberCell() {
	return <div>@TODO</div>
}

DataGridQuestionNumberCell.defaultProps = {
	altNumber: null
}

DataGridQuestionNumberCell.propTypes = {
	displayNumber: PropTypes.number.isRequired,
	altNumber: PropTypes.oneOfType([null, PropTypes.number])
}
