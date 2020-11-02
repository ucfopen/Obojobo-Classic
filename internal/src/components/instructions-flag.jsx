import './instructions-flag.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function InstructionsFlag({ text }) {
	return (
		<div className="instructions-flag">
			<p className="label">{text}</p>
		</div>
	)
}

InstructionsFlag.propTypes = {
	text: PropTypes.string.isRequired
}
