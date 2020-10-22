import React from 'react'
import PropTypes from 'prop-types'

export default function DefList({ items }) {
	return <div>@TODO</div>
}

DefList.defaultProps = {}

DefList.propTypes = {
	items: PropTypes.arrayOf(PropTypes.shape({ label: PropTypes.string, value: PropTypes.string })),
}
