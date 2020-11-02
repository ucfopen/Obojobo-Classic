import React from 'react'
import PropTypes from 'prop-types'

export default function FormDateTime() {
	return <div>@TODO</div>
}

FormDateTime.propTypes = {
	value: PropTypes.oneOfType([null, PropTypes.number]).isRequired,
	onChange: PropTypes.func.isRequired
}
