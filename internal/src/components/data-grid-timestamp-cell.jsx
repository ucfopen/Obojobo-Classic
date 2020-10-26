import './data-grid-timestamp-cell.scss'

import React, { useState, useCallback } from 'react'
import PropTypes from 'prop-types'

const formatAMPM = (date, showSeconds) => {
	var hours = date.getHours()
	var minutes = date.getMinutes()
	var seconds = date.getSeconds()
	var ampm = hours >= 12 ? 'PM' : 'AM'
	hours = hours % 12
	hours = hours ? hours : 12 // the hour '0' should be '12'
	minutes = minutes < 10 ? '0' + minutes : minutes
	seconds = seconds < 10 ? '0' + seconds : seconds
	var strTime = hours + ':' + minutes + (showSeconds ? ':' + seconds : '') + ' ' + ampm
	return strTime
}

const DataGridTimestampCell = ({ value: timestamp, display, showSeconds }) => {
	if (timestamp === null) {
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
			datetime={date.toISOString()}
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
	value: PropTypes.oneOfType([null, PropTypes.number]).isRequired,
	showSeconds: PropTypes.bool.isRequired
}

export default DataGridTimestampCell
