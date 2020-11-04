import './form-date-time.scss'

import React from 'react'
import PropTypes from 'prop-types'

export default function FormDateTime({ value, onChange }) {
	const dateTime = new Date(value)
	const [date, setDate] = React.useState(dateTime.toISOString().substring(0, 10))
	const [time, setTime] = React.useState(
		dateTime
			.getHours()
			.toString()
			.padStart(2, '0') +
			':' +
			dateTime
				.getMinutes()
				.toString()
				.padStart(2, '0')
	)

	const updateDayTime = event => {
		const { type, value } = event.target

		let updatedDateTime
		if (type === 'date') {
			setDate(value)
			updatedDateTime = new Date(value + ' ' + time)
		} else {
			setTime(value)
			updatedDateTime = new Date(date + ' ' + value)
		}

		if (updatedDateTime.toString() !== 'Invalid Date') {
			onChange(updatedDateTime.getTime())
		}
	}

	return (
		<div className="form-date-time">
			<input type="date" value={date} onChange={updateDayTime} />
			<span>at</span>
			<input type="time" value={time} onChange={updateDayTime} />
			<span>ETS</span>
		</div>
	)
}

FormDateTime.propTypes = {
	value: PropTypes.oneOfType([null, PropTypes.number]).isRequired,
	onChange: PropTypes.func.isRequired
}
