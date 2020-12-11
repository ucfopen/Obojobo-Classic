import './loading-indicator.scss'

import React from 'react'
import PropTypes from 'prop-types'

const LoadingIndicator = ({ isLoading }) => {
	if (!isLoading) return null

	return (
		<div className="repository--loading-indicator" aria-label="Loading content">
			<div className="throbber" aria-hidden={true}>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					viewBox="0 0 100 100"
					preserveAspectRatio="xMidYMid"
				>
					<circle
						cx="50"
						cy="50"
						fill="none"
						strokeWidth="10"
						r="35"
						strokeDasharray="60 110"
						transform="rotate(0.82332 50 50)"
					></circle>
				</svg>
			</div>
		</div>
	)
}

LoadingIndicator.defaultProps = {
	isLoading: false
}

LoadingIndicator.propTypes = {
	isLoading: PropTypes.bool
}

export default LoadingIndicator
