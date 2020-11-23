import './data-grid-score-cell.scss'

import React from 'react'
import PropTypes from 'prop-types'

export default function DataGridScoreCell({ value }) {
	const className = value === null ? '' : 'score-' + value
	return (
		<div className="data-grid-score-cell">
			<div className={className}>{value === null ? '--' : value + '%'}</div>
		</div>
	)
}

DataGridScoreCell.propTypes = {
	value: PropTypes.number
}
