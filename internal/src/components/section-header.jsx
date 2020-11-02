import './section-header.scss'
import React from 'react'
import PropTypes from 'prop-types'

export default function SectionHeader(props) {
	return <h3 className="section-header">{props.label}</h3>
}

SectionHeader.propTypes = {
	label: PropTypes.string.isRequired
}
