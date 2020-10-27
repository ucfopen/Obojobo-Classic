import React from 'react'
import PropTypes from 'prop-types'
import flashToHTML from '../util/flash-to-html'

export default function FlashHTML({ value }) {
	return <div dangerouslySetInnerHTML={{ __html: flashToHTML(value) }} />
}

FlashHTML.propTypes = {
	value: PropTypes.string.isRequired
}
