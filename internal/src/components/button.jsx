import React from 'react'
import PropTypes from 'prop-types'
import './button.scss'

const Button = ({text, onClick}) => (
	<div className="button" onClick={onClick}>{text}</div>
)

Button.propTypes = {
	text: PropTypes.string,
	onClick: PropTypes.func,
	type: PropTypes.oneOf(['simple', 'text'])
}

export default Button
