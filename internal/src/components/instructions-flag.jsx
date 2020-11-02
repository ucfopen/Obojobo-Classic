import React from 'react'
import PropTypes from 'prop-types'

export default function InstructionsFlag({ text }) {
	return <div>{text}</div>
}

InstructionsFlag.propTypes = {
	text: PropTypes.string.isRequired
}
