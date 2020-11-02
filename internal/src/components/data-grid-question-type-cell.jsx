import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridQuestionTypeCell() {
	return <div>@TODO</div>
}

DataGridQuestionTypeCell.propTypes = {
	value: PropTypes.oneOf(['MC', 'QA', 'Media']).isRequired
}
