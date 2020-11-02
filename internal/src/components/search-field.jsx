import React from 'react'
import PropTypes from 'prop-types'

import './search-field.scss'

export default function SearchField(props) {
	return (
		<div className="search-field-wrapper">
			<i className="magnifier-icon"></i>
			<input
				className="search-field"
				type="text"
				placeholder={props.placeholder}
				value={props.value}
				onChange={props.onChange}
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
