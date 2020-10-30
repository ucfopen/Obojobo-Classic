import './instructions-flag.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function InstructionsFlag(props) {
	return (
		<div className="instructions-flag">
			<p className="label">{props.label}</p>
		</div>
	)
}

InstructionsFlag.defaultProps = {}

InstructionsFlag.propTypes = {
	label: PropTypes.string.isRequired
}
