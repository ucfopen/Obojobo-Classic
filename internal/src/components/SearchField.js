import React from 'react'
import PropTypes from 'prop-types'

export default function SearchField({ value }) {
	return <div>@TODO</div>
}

SearchField.defaultProps = {
	value: '',
}

SearchField.propTypes = {
	value: PropTypes.string,
	onClickClear: PropTypes.func.isRequired,
}
