import React from 'react'
import PropTypes from 'prop-types'

export default function DataGrid({ type, items, selectedIndex, onSelectIndex }) {
	return <div>@TODO</div>
}

DataGrid.defaultProps = {
	items: null,
	selectedIndex: null,
}

DataGrid.propTypes = {
	onSelectIndex: PropTypes.func.isRequired,
	items: PropTypes.oneOfType([null, PropTypes.array]).isRequired,
	type: PropTypes.oneOf(['instance', 'object', 'library', 'media']).isRequired,
	selectedIndex: PropTypes.oneOfType([null, PropTypes.number]).isRequired,
	optionalObjectWithShape: PropTypes.shape({
		color: PropTypes.string,
		fontSize: PropTypes.number,
	}),
}
