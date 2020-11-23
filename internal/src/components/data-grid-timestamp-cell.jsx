import './data-grid-timestamp-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'

const formatAMPM = (date, showSeconds) => {
	let hours = date.getHours()
	let minutes = date.getMinutes()
	let seconds = date.getSeconds()
	const ampm = hours >= 12 ? 'PM' : 'AM'

	hours = hours % 12
	hours = hours ? hours : 12 // the hour '0' should be '12'
	minutes = minutes < 10 ? '0' + minutes : minutes
	seconds = seconds < 10 ? '0' + seconds : seconds

	return hours + ':' + minutes + (showSeconds ? ':' + seconds : '') + ' ' + ampm
}

const DataGridTimestampCell = ({ value: timestamp, display, showSeconds }) => {
	if (!timestamp || timestamp === '0') {
		return <div className={`repository--date-time-cell is-display-blank`}>--</div>
	}

	const date = new Date(timestamp * 1000)
	const mmyydd =
		(date.getMonth() > 8 ? date.getMonth() + 1 : '0' + (date.getMonth() + 1)) +
		'/' +
		(date.getDate() > 9 ? date.getDate() : '0' + date.getDate()) +
		'/' +
		`${date.getFullYear()}`.substr(2)
	const hhmm = formatAMPM(date, showSeconds)

	return (
		<time
			className={`repository--date-time-cell is-display-${display}`}
			dateTime={date.toISOString()}
		>
			<span className="day">{mmyydd}</span>
			<span className="time">{hhmm}</span>
		</time>
	)
}

DataGridTimestampCell.defaultProps = {
	display: 'vertical',
	showSeconds: false
}

DataGridTimestampCell.propTypes = {
	display: PropTypes.oneOf(['horizontal', 'vertical']),
	value: PropTypes.oneOfType([null, PropTypes.number]),
	showSeconds: PropTypes.bool.isRequired
}

export default DataGridTimestampCell
