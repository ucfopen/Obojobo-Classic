import './def-list.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function DefList(props) {
	return (
		<div className="repository--def-list">
			{props.items.map((item, index) => (
				<div className="row" key={item.label || `i-${index}`}>
					<p className="label">{item.label}</p>
					<p className="value">{item.value}</p>
				</div>
			))}
		</div>
	)
}

DefList.defaultProps = {}

DefList.propTypes = {
	items: PropTypes.arrayOf(
		PropTypes.shape({
			label: PropTypes.string,
			value: PropTypes.oneOfType([PropTypes.string, PropTypes.number])
		})
	)
}
