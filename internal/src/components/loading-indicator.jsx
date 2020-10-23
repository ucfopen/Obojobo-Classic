import React from 'react'
import PropTypes from 'prop-types'

const LoadingIndicator =  ({isLoading = false}) => {
	if(isLoading) {
		return <div>Loading... (@TODO, Replace with graphic)</div>
	}

	return null
}

LoadingIndicator.defaultProps = {
	isLoading: false
}

LoadingIndicator.propTypes = {
	isLoading: PropTypes.bool
}

export default LoadingIndicator
