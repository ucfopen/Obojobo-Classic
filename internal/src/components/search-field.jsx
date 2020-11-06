import React from 'react'
import PropTypes from 'prop-types'

import './search-field.scss'

export default function SearchField(props) {
	return (
		<div className="repository--search-field">
			<i className="magnifier-icon"></i>
			<input
				className="search-field"
				type="text"
				placeholder={props.placeholder}
				value={props.value}
				onChange={event => props.onChange(event.target.value)}
			/>
		</div>
	)
}

SearchField.defaultProps = {
	value: ''
}

SearchField.propTypes = {
	value: PropTypes.string,
	placeholder: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired
}
