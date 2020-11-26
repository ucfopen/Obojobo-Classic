import './data-grid-date-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'

const DataGridDateCell = ({ value: timestamp }) => {
	if (!timestamp || timestamp === '0') {
		return <div className={`repository--date-cell is-display-blank`}>--</div>
	}

	const display = React.useMemo(() => {
		const date = new Date(timestamp * 1000)

		return {
			mmdd:
				(date.getMonth() > 8 ? date.getMonth() + 1 : '0' + (date.getMonth() + 1)) +
				'/' +
				(date.getDate() > 9 ? date.getDate() : '0' + date.getDate()),
			year: date.getFullYear(),
			iso: date.toISOString()
		}
	})

	return (
		<time className="repository--date-time-cell" dateTime={display.iso}>
			<span className="day">{display.mmdd}</span>
			<span className="year">{display.year}</span>
		</time>
	)
}

DataGridDateCell.propTypes = {
	value: PropTypes.number
}

export default DataGridDateCell
