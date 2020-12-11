import './form-date-time.scss'

import React from 'react'
import PropTypes from 'prop-types'

export default function FormDateTime({ value, onChange }) {
	const dateTime = new Date(value * 1000)
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
			onChange(updatedDateTime.getTime() / 1000)
		}
	}

	return (
		<div className={`form-date-time ${value === null ? 'is-null' : 'is-not-null'}`}>
			<input
				disabled={value === null}
				type="date"
				value={value === null ? '' : date}
				onChange={updateDayTime}
			/>
			<span>at</span>
			<input
				disabled={value === null}
				type="time"
				value={value === null ? '' : time}
				onChange={updateDayTime}
			/>
			<span>EST</span>
		</div>
	)
}

FormDateTime.propTypes = {
	value: PropTypes.number,
	onChange: PropTypes.func.isRequired
}
