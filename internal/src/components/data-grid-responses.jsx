import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridResponses() {
	return <div>@TODO</div>
}

DataGridResponses.propTypes = {
	responses: PropTypes.arrayOf(
		PropTypes.shape({
			userName: PropTypes.string,
			response: PropTypes.string,
			score: PropTypes.number,
			time: PropTypes.number
		})
	)
}
