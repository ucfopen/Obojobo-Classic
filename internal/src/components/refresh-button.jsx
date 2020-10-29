import './refresh-button.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function RefreshButton({ onClick }) {
	return <button onClick={onClick} className="refresh-button" title="refresh"></button>
}

RefreshButton.propTypes = {
	onClick: PropTypes.func.isRequired
}
