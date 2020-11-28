import './attempt-details.scss'

import React, { useMemo } from 'react'
import PropTypes from 'prop-types'
import DefList from './def-list'
import dayjs from 'dayjs'
import humanizeDuration from 'humanize-duration'

export default function AttemptDetails({
	attemptNumber,
	score,
	numAnsweredQuestions,
	numTotalQuestions,
	startTime,
	endTime
}) {
	const items = useMemo(() => {
		const startD = dayjs(startTime * 1000)
		const endD = dayjs(endTime * 1000)
		const items = [
			{ label: 'Attempt Score', value: `${score}%` },
			{ label: 'Answered Questions', value: `${numAnsweredQuestions} out of ${numTotalQuestions}` },
			{ label: 'Start Time', value: startD.format('MM/DD/YY - hh:mm:ss A') },
			{ label: 'End Time', value: endD.format('MM/DD/YY - hh:mm:ss A') },
			{ label: 'Duration', value: humanizeDuration((endTime - startTime) * 1000, { largest: 2 }) }
		]
		return items
	}, [score, numAnsweredQuestions, numTotalQuestions, startTime, endTime])

	return (
		<section className="repository--attempt-details">
			<h1>Attempt {attemptNumber}</h1>
			<DefList items={items} />
		</section>
	)
}

AttemptDetails.propTypes = {
	attemptNumber: PropTypes.number.isRequired,
	score: PropTypes.number.isRequired,
	numAnsweredQuestions: PropTypes.number.isRequired,
	numTotalQuestions: PropTypes.number.isRequired,
	startTime: PropTypes.number.isRequired,
	endTime: PropTypes.number.isRequired
}
