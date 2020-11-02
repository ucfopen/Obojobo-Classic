import React from 'react'
import PropTypes from 'prop-types'

export default function AttemptDetails() {
	return <div>@TODO</div>
}

AttemptDetails.propTypes = {
	attemptNumber: PropTypes.number.isRequired,
	score: PropTypes.number.isRequired,
	numAnsweredQuestions: PropTypes.number.isRequired,
	numTotalQuestions: PropTypes.number.isRequired,
	startTime: PropTypes.number.isRequired,
	endTime: PropTypes.number.isRequired
}
