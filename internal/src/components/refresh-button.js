import React from 'react'
import PropTypes from 'prop-types'

export default function RefreshButton({ onClick }) {
	return <button onClick={onClick}>Refresh Button @TODO</button>
}

RefreshButton.propTypes = {
	onClick: PropTypes.func.isRequired,
}
