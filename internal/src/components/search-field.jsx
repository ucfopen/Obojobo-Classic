import React from 'react'
import PropTypes from 'prop-types'

export default function SearchField() {
	return <div>Search Field @TODO</div>
}

SearchField.defaultProps = {
	value: ''
}

SearchField.propTypes = {
	value: PropTypes.string,
	placeholder: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired
}
