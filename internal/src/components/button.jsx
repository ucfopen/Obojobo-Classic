import './button.scss'

import React from 'react'
import PropTypes from 'prop-types'

const Button = ({ text, type, onClick }) => (
	<div className={`repository--button is-type-${type}`} onClick={onClick}>
		{text}
	</div>
)

Button.propTypes = {
	text: PropTypes.string,
	onClick: PropTypes.func,
	type: PropTypes.oneOf(['text', 'text-bold', 'small', 'large', 'alt']).isRequired
}

export default Button
