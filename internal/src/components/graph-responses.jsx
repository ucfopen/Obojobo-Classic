import React from 'react'
import PropTypes from 'prop-types'

export default function GraphResponses() {
	return <div>@TODO</div>
}

GraphResponses.propTypes = {
	data: PropTypes.arrayOf(
		PropTypes.shape({
			label: PropTypes.string,
			amount: PropTypes.number,
			isCorrect: PropTypes.bool
		})
	)
}
