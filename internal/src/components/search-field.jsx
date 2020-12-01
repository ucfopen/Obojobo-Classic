import React from 'react'
import PropTypes from 'prop-types'

import './search-field.scss'

export default function SearchField({ value, onChange, placeholder }) {
	const handleChange = React.useCallback(
		e => {
			if (onChange) onChange(e.target.value)
		},
		[onChange]
	)

	return (
		<div className={'repository--search-field ' + (value ? 'is-not-empty' : 'is-empty')}>
			<i className="magnifier-icon"></i>
			<input
				className="search-field"
				type="search"
				name="search"
				placeholder={placeholder}
				value={value}
				onChange={handleChange}
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
